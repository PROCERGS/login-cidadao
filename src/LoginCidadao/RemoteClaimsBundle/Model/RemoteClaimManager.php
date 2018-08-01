<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Service\AuthorizationManager;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorization;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimAuthorizationRepository;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository;

class RemoteClaimManager implements RemoteClaimManagerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var RemoteClaimAuthorizationRepository */
    private $remoteClaimAuthorizationRepository;

    /** @var RemoteClaimRepository */
    private $remoteClaimRepository;

    /** @var AuthorizationManager */
    private $authorizationManager;

    /**
     * RemoteClaimManager constructor.
     * @param EntityManagerInterface $em
     * @param RemoteClaimAuthorizationRepository $remoteClaimAuthorizationRepository
     * @param RemoteClaimRepository $remoteClaimRepository
     * @param AuthorizationManager $authorizationManager
     */
    public function __construct(
        EntityManagerInterface $em,
        RemoteClaimAuthorizationRepository $remoteClaimAuthorizationRepository,
        RemoteClaimRepository $remoteClaimRepository,
        AuthorizationManager $authorizationManager
    ) {
        $this->em = $em;
        $this->remoteClaimAuthorizationRepository = $remoteClaimAuthorizationRepository;
        $this->remoteClaimRepository = $remoteClaimRepository;
        $this->authorizationManager = $authorizationManager;
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function enforceAuthorization(RemoteClaimAuthorizationInterface $authorization)
    {
        $remoteClaim = $this->getExistingRemoteClaim($authorization->getClaimName());
        $this->enforceImplicitAuthorization($authorization, $remoteClaim);

        $existingAuthorization = $this->remoteClaimAuthorizationRepository->findAuthorization($authorization);
        if ($existingAuthorization instanceof RemoteClaimAuthorizationInterface) {
            return $existingAuthorization;
        }

        $this->em->persist($authorization);

        return $authorization;
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isAuthorized($claimName, PersonInterface $person, ClientInterface $client)
    {
        if (!$claimName instanceof TagUri) {
            $claimName = TagUri::createFromString($claimName);
        }
        $authorization = (new RemoteClaimAuthorization())
            ->setClaimName($claimName)
            ->setPerson($person)
            ->setClient($client);
        $existingAuthorization = $this->remoteClaimAuthorizationRepository->findAuthorization($authorization);

        return $existingAuthorization instanceof RemoteClaimAuthorizationInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAllAuthorizations(Authorization $authorization)
    {
        $remoteClaimAuthorizations = $this->remoteClaimAuthorizationRepository
            ->findAllByClientAndPerson($authorization->getClient(), $authorization->getPerson());

        foreach ($remoteClaimAuthorizations as $remoteClaimAuthorization) {
            $this->em->remove($remoteClaimAuthorization);
        }
        $this->em->flush();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function filterRemoteClaims($scopes)
    {
        $returnString = is_string($scopes);
        if ($returnString) {
            $scopes = explode(' ', $scopes);
        }

        $response = [];
        foreach ($scopes as $scope) {
            try {
                TagUri::createFromString($scope);
            } catch (\InvalidArgumentException $e) {
                $response[] = $scope;
            }
        }

        if ($returnString) {
            return implode(' ', $response);
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function getExistingRemoteClaim(TagUri $claimName)
    {
        return $this->remoteClaimRepository->findOneBy([
            'name' => $claimName,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getRemoteClaimsFromAuthorization(Authorization $authorization)
    {
        return $this->remoteClaimRepository
            ->findByClientAndPerson($authorization->getClient(), $authorization->getPerson());
    }

    /**
     * @inheritDoc
     */
    public function getRemoteClaimsAuthorizationsFromAuthorization(Authorization $authorization)
    {
        return $this->remoteClaimAuthorizationRepository->findAllByClientAndPerson(
            $authorization->getClient(), $authorization->getPerson()
        );
    }

    /**
     * @inheritDoc
     */
    public function getRemoteClaimsWithTokens(ClientInterface $client, PersonInterface $person)
    {
        /** @var RemoteClaimAuthorizationInterface[] $remoteClaimAuthorizations */
        $remoteClaimAuthorizations = $this->remoteClaimAuthorizationRepository
            ->findAllByClientAndPerson($client, $person);

        /** @var RemoteClaimInterface[] $remoteClaims */
        $remoteClaims = $this->remoteClaimRepository->findByClientAndPerson($client, $person);

        $response = [];
        foreach ($remoteClaimAuthorizations as $authorization) {
            $tag = $this->getTagString($authorization->getClaimName());
            $response[$tag]['authorization'] = $authorization;
        }
        foreach ($remoteClaims as $remoteClaim) {
            $tag = $this->getTagString($remoteClaim->getName());
            $response[$tag]['remoteClaim'] = $remoteClaim;
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function getRemoteClaimAuthorizationByAccessToken(ClaimProviderInterface $claimProvider, $accessToken)
    {
        return $this->remoteClaimAuthorizationRepository->findOneBy([
            'claimProvider' => $claimProvider,
            'accessToken' => $accessToken,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function updateRemoteClaimUri(TagUri $claimName, $uri)
    {
        $remoteClaim = $this->getExistingRemoteClaim($claimName);

        if (!$remoteClaim instanceof RemoteClaimInterface) {
            // TODO: log
            // TODO: throw exception?
            return null;
        }

        $remoteClaim->setUri($uri);
        $this->em->flush();

        return $remoteClaim;
    }

    /**
     * @inheritDoc
     */
    public function enforceImplicitAuthorization(
        RemoteClaimAuthorizationInterface $claimAuthorization,
        RemoteClaimInterface $remoteClaim
    ): Authorization {
        /** @var ClaimProviderInterface|ClientInterface $provider */
        $provider = $claimAuthorization->getClaimProvider();

        return $this->authorizationManager->enforceAuthorization(
            $claimAuthorization->getPerson(),
            $provider,
            array_merge($remoteClaim->getEssentialScope(), $remoteClaim->getRecommendedScope()),
            AuthorizationManager::SCOPE_MERGE
        );
    }

    /**
     * @param string|TagUri $tag
     * @return string
     */
    private function getTagString($tag)
    {
        return is_string($tag) ? $tag : $tag->__toString();
    }
}
