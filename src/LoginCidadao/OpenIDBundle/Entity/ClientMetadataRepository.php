<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ClientMetadataRepository extends EntityRepository
{
    public function findByPostLogoutRedirectUri($uri)
    {
        /** @var ClientMetadata[] $results */
        $results = $this->createQueryBuilder('m')
            ->where('m.post_logout_redirect_uris LIKE :uri')
            ->setParameter('uri', "%$uri%")
            ->getQuery()->getResult();

        $response = [];
        foreach ($results as $metadata) {
            if (false !== array_search($uri, $metadata->getPostLogoutRedirectUris())) {
                $response[] = $metadata;
            }
        }

        return $response;
    }
}
