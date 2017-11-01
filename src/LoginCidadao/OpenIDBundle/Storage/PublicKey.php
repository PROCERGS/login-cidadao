<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Storage;

use Gaufrette\Filesystem;
use Doctrine\ORM\EntityManager;
use Gaufrette\Exception\FileNotFound;
use OAuth2\Storage\PublicKeyInterface;

class PublicKey implements PublicKeyInterface
{
    /** @var EntityManager */
    private $em;

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $fileName;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function setFilesystem(Filesystem $filesystem,
                                    $fileName = 'private.pem')
    {
        $this->filesystem = $filesystem;
        $this->fileName   = $fileName;
    }

    public function getEncryptionAlgorithm($client_id = null)
    {
        return 'RS256';
    }

    public function getPrivateKey($client_id = null)
    {
        $key = $this->getPrivateKeyResource();
        openssl_pkey_export($key, $priv);
        return $priv;
    }

    public function getPublicKey($client_id = null)
    {
        $details = openssl_pkey_get_details($this->getPrivateKeyResource());

        return $details['key'];
    }

    /**
     * @return \Gaufrette\File
     */
    private function createKeys()
    {
        $priv = null;
        $key  = openssl_pkey_new();
        openssl_pkey_export($key, $priv);
        $file = $this->filesystem->get($this->fileName, true);
        $file->setContent($priv);

        return $file;
    }

    /**
     * @return resource
     */
    private function getPrivateKeyResource()
    {
        try {
            $file = $this->filesystem->get($this->fileName);
        } catch (FileNotFound $e) {
            $file = $this->createKeys();
        }

        $key = openssl_pkey_get_private($file->getContent());

        return $key;
    }
}
