<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Storage;

use Doctrine\ORM\EntityManager;
use OAuth2\Storage\PublicKeyInterface;

class PublicKey implements PublicKeyInterface
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getEncryptionAlgorithm($client_id = null)
    {
        return 'RS256';
    }

    public function getPrivateKey($client_id = null)
    {
        $key     = openssl_pkey_new();
        openssl_pkey_export($key, $priv);
        return $priv;
    }

    public function getPublicKey($client_id = null)
    {
        //
    }
}
