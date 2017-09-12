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

use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\FOSRestController;
use phpseclib\Crypt\RSA;

/**
 * @REST\Route("/openid/connect")
 */
class JsonWebKeyController extends FOSRestController
{

    /**
     * @REST\Get("/jwks", name="oidc_jwks", defaults={"_format"="json"})
     * @REST\View(templateVar="jwks")
     */
    public function getAction()
    {
        $keyStorage = $this->get('oauth2.storage.public_key');
        $pubKey     = new RSA();
        $pubKey->loadKey($keyStorage->getPublicKey());
        $publicKey  = \JOSE_JWK::encode($pubKey);

        $publicKey->components['kid'] = 'pub';

        $jwks = new \JOSE_JWKSet(array($publicKey));

        return new JsonResponse(json_decode($jwks->toString()));
    }
}
