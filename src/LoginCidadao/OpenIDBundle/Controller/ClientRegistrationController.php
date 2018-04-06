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

use LoginCidadao\OAuthBundle\Entity\Client;
use JMS\Serializer\SerializationContext;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Form\ClientMetadataForm;
use LoginCidadao\OpenIDBundle\Exception\DynamicRegistrationException;

class ClientRegistrationController extends FOSRestController
{

    /**
     * @REST\Post("/openid/connect/register", name="oidc_dynamic_registration", defaults={"_format"="json"})
     * @REST\View(templateVar="client")
     */
    public function registerAction(Request $request)
    {
        $this->parseJsonRequest($request);

        $data = new ClientMetadata();
        $form = $this->createForm(new ClientMetadataForm(), $data, ['cascade_validation' => true]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $metadata = $form->getData();
            $client = $this->getClientManager()->register($metadata);

            return $this->view($metadata->fromClient($client), 201);
        } else {
            $error = $this->handleFormErrors($form->getErrors(true));

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

        $context = SerializationContext::create()->setGroups("client_metadata");

        $view = $this->view($client->getMetadata())->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * @param \Symfony\Component\Form\FormError[] $errors
     * @return DynamicRegistrationException
     */
    private function handleFormErrors($errors)
    {
        foreach ($errors as $error) {
            $cause = $error->getCause();
            $value = $cause->getInvalidValue();
            $propertyRegex = '/^data\\.([a-zA-Z0-9_]+).*$/';
            $property = preg_replace(
                $propertyRegex,
                '$1',
                $cause->getPropertyPath()
            );
            //$property      = str_replace('data.', '', $cause->getPropertyPath());

            switch ($property) {
                case 'redirect_uris':
                    return new DynamicRegistrationException(
                        'Invalid redirect URIs: '.$cause->getMessage(),
                        DynamicRegistrationException::ERROR_INVALID_REDIRECT_URI
                    );
                case 'sector_identifier_uri':
                    return new DynamicRegistrationException(
                        "Invalid value for '{$property}': {$cause->getMessage()}",
                        DynamicRegistrationException::ERROR_INVALID_CLIENT_METADATA
                    );
                default:
                    return new DynamicRegistrationException(
                        "Invalid value for '{$property}'='{$value}': {$cause->getMessage()}",
                        DynamicRegistrationException::ERROR_INVALID_CLIENT_METADATA
                    );
            }
        }
    }

    private function parseJsonRequest(Request $request)
    {
        $request->setFormat('json', 'application/json');
        if (0 === strpos(
                $request->headers->get('Content-Type'),
                'application/json'
            )
        ) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }
    }

    /**
     * @param string $clientId
     * @return ClientInterface
     * @throws DynamicRegistrationException
     */
    private function getClientOr404($clientId)
    {
        $parts = explode('_', $clientId, 2);
        if (count($parts) !== 2) {
            throw new DynamicRegistrationException(
                "Invalid client_id",
                DynamicRegistrationException::ERROR_INVALID_CLIENT_METADATA
            );
        }
        $entityId = $parts[0];
        $publicId = $parts[1];

        /** @var ClientInterface $client */
        $client = $this->getDoctrine()->getRepository('LoginCidadaoOAuthBundle:Client')
            ->findOneBy(array('id' => $entityId, 'randomId' => $publicId));

        if (!$client) {
            throw $this->createNotFoundException('Client not found.');
        }

        return $client;
    }

    private function checkRegistrationAccessToken(Request $request, Client $client)
    {
        $raw = $request->get(
            'access_token',
            $request->headers->get('authorization')
        );

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
}
