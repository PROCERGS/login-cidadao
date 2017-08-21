<?php

namespace LoginCidadao\OpenIDBundle\Controller;

use LoginCidadao\OAuthBundle\Entity\Client;
use JMS\Serializer\SerializationContext;
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
            $client = $this->registerClient($metadata);

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

    /**
     * @param ClientMetadata $data
     * @return Client
     */
    private function registerClient(ClientMetadata $data)
    {
        $em = $this->getDoctrine()->getManager();
        if ($data->getClient() === null) {
            $client = $data->toClient();
        } else {
            $client = $data->getClient();
        }

        if ($client->getName() === null) {
            $firstUrl = $this->getHost($client->getRedirectUris()[0]);
            $client->setName($firstUrl);
        }
        if ($client->getDescription() === null) {
            $client->setDescription('');
        }
        if ($client->getTermsOfUseUrl() === null) {
            $client->setTermsOfUseUrl('');
        }
        if ($client->getSiteUrl() === null) {
            $client->setSiteUrl('');
        }

        if (count($data->getContacts()) > 0) {
            $owners = $em->getRepository($this->getParameter('user.class'))
                ->findByEmail($data->getContacts());

            foreach ($owners as $person) {
                if ($person->getConfirmationToken() !== null) {
                    continue;
                }
                $client->getOwners()->add($person);
            }
        }

        $publicScopes = explode(' ', $this->getParameter('lc_public_scopes'));
        $client->setAllowedScopes($publicScopes);

        $em->persist($client);

        $data->setClient($client);
        $em->persist($data);

        $em->flush();

        return $client;
    }

    private function getHost($uri)
    {
        return parse_url($uri, PHP_URL_HOST);
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
     * @return Client
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

        $client = $this->getDoctrine()->getRepository('LoginCidadaoOAuthBundle:Client')
            ->findOneBy(array('id' => $entityId, 'randomId' => $publicId));

        if (!$client) {
            throw $this->createNotFoundException('Client not found.');
        }

        return $client;
    }

    private function checkRegistrationAccessToken(
        Request $request,
        Client $client
    ) {
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
}
