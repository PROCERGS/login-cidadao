<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\ResponseType;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use OAuth2\OpenID\ResponseType\IdToken as BaseIdToken;
use OAuth2\Storage\PublicKeyInterface;
use OAuth2\Encryption\EncryptionInterface;

class IdToken extends BaseIdToken
{
    /** @var PublicKeyInterface */
    protected $publicKeyStorage;

    /** @var EncryptionInterface */
    protected $encryptionUtil;

    /** @var SubjectIdentifierService */
    private $subjectIdentifierService;

    /** @var ClientManager */
    private $clientManager;

    /** @var UserManager */
    private $userManager;

    protected function encodeToken(array $token, $client_id = null)
    {
        $private_key = $this->publicKeyStorage->getPrivateKey($client_id);
        $algorithm = $this->publicKeyStorage->getEncryptionAlgorithm($client_id);

        $token['kid'] = 'pub';

        return $this->encryptionUtil->encode($token, $private_key, $algorithm);
    }

    /**
     * Create id token
     *
     * @param string $client_id
     * @param mixed $userInfo
     * @param mixed $nonce
     * @param mixed $userClaims
     * @param mixed $access_token
     * @return mixed|string
     */
    public function createIdToken($client_id, $userInfo, $nonce = null, $userClaims = null, $access_token = null)
    {
        $userInfo = $this->handleSubjectIdentifier($userInfo, $client_id);

        return parent::createIdToken($client_id, $userInfo, $nonce, $userClaims, $access_token);
    }

    private function handleSubjectIdentifier($userInfo, $clientId)
    {
        $client = $this->clientManager->getClientById($clientId);
        $metadata = $client->getMetadata();

        if (is_array($userInfo)) {
            if (!isset($userInfo['user_id'])) {
                throw new \LogicException('if $user_id argument is an array, user_id index must be set');
            }

            $userInfo['user_id'] = $this->getSub($metadata, $userInfo['user_id']);
        } else {
            $userInfo = $this->getSub($metadata, $userInfo);
        }

        return $userInfo;
    }

    private function getSub(ClientMetadata $metadata, $userId)
    {
        /** @var PersonInterface $person */
        $person = $this->userManager->findUserBy(['id' => $userId]);

        return $this->subjectIdentifierService->getSubjectIdentifier($person, $metadata);
    }

    public function setUserManager(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function setClientManager(ClientManager $clientManager)
    {
        $this->clientManager = $clientManager;
    }

    public function setSubjectIdentifierService(SubjectIdentifierService $subjectIdentifierService)
    {
        $this->subjectIdentifierService = $subjectIdentifierService;
    }
}
