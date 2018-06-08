<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Storage;

use Gaufrette\Exception\FileNotFound;
use Gaufrette\File;
use Gaufrette\Filesystem;
use LoginCidadao\OpenIDBundle\Storage\PublicKey;
use PHPUnit\Framework\TestCase;

class PublicKeyTest extends TestCase
{
    public function testPublicKey()
    {
        $filename = 'key.pem';

        $file = null;

        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $filesystem */
        $filesystem = $this->getMockBuilder('Gaufrette\Filesystem')->disableOriginalConstructor()->getMock();
        $filesystem->expects($this->atLeastOnce())
            ->method('get')->with($filename, $this->isType('bool'))
            ->willReturnCallback(function ($key, $create = false) use ($filename, $filesystem, &$file) {
                $this->assertSame($filename, $key);
                if ($file !== null) {
                    return $file;
                } elseif ($create === false) {
                    throw new FileNotFound($filename);
                } else {
                    $file = new File($key, $filesystem);

                    return $file;
                }
            });

        $publicKey = new PublicKey();
        $publicKey->setFilesystem($filesystem, $filename);
        $pubKey = $publicKey->getPublicKey('clientId');
        $privKey = $publicKey->getPrivateKey('clientId');

        $this->assertNotNull($pubKey);
        $this->assertNotNull($privKey);
        $this->assertSame('RS256', $publicKey->getEncryptionAlgorithm('clientId'));
    }
}
