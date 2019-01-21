<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Manager;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Event\GetClientEvent;
use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Model\CreateClientRequest;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ClientManager
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var EntityManagerInterface */
    private $em;

    /** @var PersonRepository */
    private $personRepository;

    /** @var string */
    private $publicScopes;

    /**
     * ClientManager constructor.
     * @param EntityManagerInterface $em
     * @param EventDispatcherInterface $dispatcher
     * @param PersonRepository $personRepository
     * @param $publicScopes
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        PersonRepository $personRepository,
        $publicScopes
    ) {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->personRepository = $personRepository;
        $this->publicScopes = $publicScopes;
    }

    /**
     * @param mixed $id
     * @return ClientInterface|null
     */
    public function getClientById($id)
    {
        if ($id === null) {
            return null;
        }
        $randomId = null;
        if (strstr($id, '_') !== false) {
            $parts = explode('_', $id);
            $id = $parts[0];
            $randomId = $parts[1];
        }

        $repo = $this->em->getRepository('LoginCidadaoOAuthBundle:Client');

        if ($randomId) {
            $client = $repo->findOneBy([
                'id' => $id,
                'randomId' => $randomId,
            ]);
        } else {
            $client = $repo->find($id);
        }
        $event = new GetClientEvent($client);
        $this->dispatcher->dispatch(LoginCidadaoCoreEvents::GET_CLIENT, $event);

        return $event->getClient();
    }

    /**
     * @param ClientMetadata $data
     * @return ClientInterface
     * @throws UniqueConstraintViolationException
     */
    public function register(ClientMetadata $data)
    {
        $client = $data->getClient();

        $this->em->persist($client);

        $data->setClient($client);
        $this->em->persist($data);

        $this->em->flush();

        return $client;
    }

    private function sanitizeClient(ClientInterface $client)
    {
        if ($client->getName() === null) {
            $firstUrl = $client->getRedirectUris()
                ? parse_url($client->getRedirectUris()[0], PHP_URL_HOST)
                : 'Unamed Client';
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

        return $client;
    }

    public function populateNewMetadata(ClientMetadata $data)
    {
        $this->initializeRegistrationAccessToken($data);

        if ($data->getClient() === null) {
            $client = $data->toClient();
        } else {
            $client = $data->getClient();
        }

        $client = $this->sanitizeClient($client);
        if ($data->getClientName() === null) {
            $data->setClientName($client->getName());
        }

        if (count($data->getContacts()) > 0) {
            /** @var PersonInterface[] $owners */
            $owners = $this->personRepository->findBy([
                'email' => $data->getContacts(),
            ]);

            foreach ($owners as $person) {
                if (!$person->getEmailConfirmedAt() instanceof \DateTime) {
                    // Email is not verified. Skipping...
                    continue;
                }
                $client->getOwners()->add($person);
            }
        }

        $publicScopes = explode(' ', $this->publicScopes);
        $client->setAllowedScopes($publicScopes);

        $data->setClient($client);

        return $data;
    }

    private function initializeRegistrationAccessToken(ClientMetadata &$data)
    {
        if (null === $data->getRegistrationAccessToken()) {
            $registrationAccessToken = bin2hex(random_bytes(120));
            $data->setRegistrationAccessToken($registrationAccessToken);
        }
    }

    public function createClientMetadata(CreateClientRequest $createClientRequest): ClientMetadata
    {
        $metadata = (new ClientMetadata())
            ->setRedirectUris($createClientRequest->redirect_uris)
            ->setResponseTypes($createClientRequest->response_types)
            ->setGrantTypes($createClientRequest->grant_types)
            ->setApplicationType($createClientRequest->application_type)
            ->setContacts($createClientRequest->contacts)
            ->setClientName($createClientRequest->client_name)
            ->setLogoUri($createClientRequest->logo_uri)
            ->setClientUri($createClientRequest->client_uri)
            ->setPolicyUri($createClientRequest->policy_uri)
            ->setTosUri($createClientRequest->tos_uri)
            ->setJwksUri($createClientRequest->jwks_uri)
            ->setJwks($createClientRequest->jwks)
            ->setSectorIdentifierUri($createClientRequest->sector_identifier_uri)
            ->setSubjectType($createClientRequest->subject_type)
            ->setIdTokenSignedResponseAlg($createClientRequest->id_token_signed_response_alg)
            ->setIdTokenEncryptedResponseAlg($createClientRequest->id_token_encrypted_response_alg)
            ->setIdTokenEncryptedResponseEnc($createClientRequest->id_token_encrypted_response_enc)
            ->setUserinfoSignedResponseAlg($createClientRequest->userinfo_signed_response_alg)
            ->setUserinfoEncryptedResponseAlg($createClientRequest->userinfo_encrypted_response_alg)
            ->setUserinfoEncryptedResponseEnc($createClientRequest->userinfo_encrypted_response_enc)
            ->setRequestObjectSigningAlg($createClientRequest->request_object_signing_alg)
            ->setRequestObjectEncryptionAlg($createClientRequest->request_object_encryption_alg)
            ->setRequestObjectEncryptionEnc($createClientRequest->request_object_encryption_enc)
            ->setTokenEndpointAuthMethod($createClientRequest->token_endpoint_auth_method)
            ->setTokenEndpointAuthSigningAlg($createClientRequest->token_endpoint_auth_signing_alg)
            ->setDefaultMaxAge($createClientRequest->default_max_age)
            ->setRequireAuthTime($createClientRequest->require_auth_time)
            ->setDefaultAcrValues($createClientRequest->default_acr_values)
            ->setInitiateLoginUri($createClientRequest->initiate_login_uri)
            ->setRequestUris($createClientRequest->request_uris)
            ->setRegistrationAccessToken($createClientRequest->registration_access_token)
            ->setPostLogoutRedirectUris($createClientRequest->post_logout_redirect_uris);

        $metadata = $this->populateNewMetadata($metadata);

        $this->em->persist($metadata);
        $this->em->flush();

        return $metadata;
    }
}
