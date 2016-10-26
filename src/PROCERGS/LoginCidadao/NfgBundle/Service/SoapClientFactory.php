<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Service;

class SoapClientFactory
{
    public static function createClient($wsdl, $verifyHttps = true)
    {
        $options = [];
        if (!$verifyHttps) {
            $options['stream_context'] = stream_context_create(
                [
                    'ssl' => [
                        // disable SSL/TLS security checks
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]
            );
        }

        return new \SoapClient($wsdl, $options);
    }
}
