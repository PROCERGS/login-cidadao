<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use FOS\RestBundle\Context\Context;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Exception\DynamicRegistrationException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ClientRegistrationController
 * @package LoginCidadao\OpenIDBundle\Controller
 * @codeCoverageIgnore
 */
class ClientRegistrationController extends FOSRestController
{

    /**
     * @REST\Post("/openid/connect/register", name="oidc_dynamic_registration", defaults={"_format"="json"})
     * @REST\View(templateVar="client")
     */
    public function registerAction(Request $request)
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        /** @var ClientMetadata $metadata */
        $metadata = $serializer->deserialize($request->getContent(), ClientMetadata::class, 'json');

        /** @var ValidatorInterface $validator */
        $validator = $this->get('validator');
        $violations = $validator->validate($metadata);

        if ($violations->count() === 0) {
            $clientManager = $this->getClientManager();
            try {
                $client = $clientManager->register($metadata);
            } catch (UniqueConstraintViolationException $e) {
                $error = new DynamicRegistrationException('Client already exists', 400);

                return $this->view($error->getData(), $error->getCode());
            }

            return $this->view($metadata->fromClient($client), 201);
        } else {
            $error = $this->handleViolations($violations);

            return $this->view($error->getData(), 400);
        }
    }

    /**
     * @REST\Get("/openid/connect/register/{clientId}", name="oidc_get_client_details", defaults={"_format"="json"})
     * @REST\View(templateVar="client")
     */
    public function getDetailsAction(Request $request, $clientId)
    {
        try {
            $client = $this->getClientOr404($clientId);
        } catch (DynamicRegistrationException $e) {
            return $this->view($e->getData(), 400);
        }
        $this->checkRegistrationAccessToken($request, $client);

        $context = (new Context())->setGroups(["client_metadata"]);
        $view = $this->view($client->getMetadata())->setContext($context);

        return $this->handleView($view);
    }

    /**
     * @param string $clientId
     * @return ClientInterface
     */
    private function getClientOr404($clientId)
    {
        /** @var ClientInterface|null $client */
        $client = $this->getClientManager()->getClientById($clientId);

        if (!$client instanceof ClientInterface) {
            throw $this->createNotFoundException('Client not found.');
        }

        return $client;
    }

    private function checkRegistrationAccessToken(Request $request, Client $client)
    {
        $raw = $request->get('access_token', $request->headers->get('authorization'));

        $token = str_replace('Bearer ', '', $raw);
        $metadata = $client->getMetadata();
        if (!$token || $metadata->getRegistrationAccessToken() !== $token) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @return ClientManager|object
     */
    private function getClientManager()
    {
        return $this->get('lc.client_manager');
    }

    /**
     * @param ConstraintViolationInterface[]|ConstraintViolationListInterface $violations
     * @return DynamicRegistrationException
     */
    private function handleViolations($violations)
    {
        foreach ($violations as $violation) {
            $property = $violation->getPropertyPath();
            $value = $violation->getInvalidValue();

            switch ($property) {
                case 'redirect_uris':
                    return new DynamicRegistrationException(
                        'Invalid redirect URIs: '.$violation->getMessage(),
                        DynamicRegistrationException::ERROR_INVALID_REDIRECT_URI
                    );
                case 'sector_identifier_uri':
                    return new DynamicRegistrationException(
                        "Invalid value for '{$property}': {$violation->getMessage()}",
                        DynamicRegistrationException::ERROR_INVALID_CLIENT_METADATA
                    );
                default:
                    return new DynamicRegistrationException(
                        "Invalid value for '{$property}'='{$value}': {$violation->getMessage()}",
                        DynamicRegistrationException::ERROR_INVALID_CLIENT_METADATA
                    );
            }
        }

        throw new \RuntimeException('No errors found but there should be at least one!');
    }
}
