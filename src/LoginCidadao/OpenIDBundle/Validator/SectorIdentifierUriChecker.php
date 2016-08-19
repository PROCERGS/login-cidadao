<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Validator;

use Doctrine\ORM\EntityManager;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SectorIdentifierUriChecker
{
    /** @var EntityManager */
    private $em;

    /**
     * SectorIdentifierUriChecker constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param ClientMetadata $metadata
     * @param $sectorIdentifierUri
     * @return bool
     * @throws HttpException
     */
    public function check(ClientMetadata $metadata, $sectorIdentifierUri)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sectorIdentifierUri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($responseCode !== 200) {
            throw new HttpException($responseCode);
        }

        $allowedUris = json_decode(trim($response));

        if (!is_array($allowedUris)) {
            return false;
        }
        
        foreach ($metadata->getRedirectUris() as $uri) {
            if (array_search($uri, $allowedUris) === false) {
                return false;
            }
        }

        return true;
    }

    public function recheck(ClientMetadata $metadata)
    {
        $url = $metadata->getSectorIdentifierUri();

        try {
            if ($url !== null && !$this->check($metadata, $url)) {
                $metadata->setOrganization(null);
                $metadata->setSectorIdentifierUri(null);
                $this->em->persist($metadata);
                $this->em->flush($metadata);
            }
        } catch (HttpException $e) {
            if ($e->getStatusCode() !== 200) {
                $metadata->setOrganization(null);
            }
        }

        return $metadata;
    }
}