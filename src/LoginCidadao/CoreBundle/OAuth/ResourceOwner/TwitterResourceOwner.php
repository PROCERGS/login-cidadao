<?php

namespace LoginCidadao\CoreBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitterResourceOwner as Base;

/**
 * TwitterResourceOwner
 *
 * This extends HIW's TwitterResourceOwner to use the force_login parameter.
 */
class TwitterResourceOwner extends Base
{

    public function getAuthorizationUrl($redirectUri,
                                        array $extraParameters = array())
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        $params = array(
            'oauth_token' => $token['oauth_token'],
            'force_login' => 'true'
        );
        return $this->normalizeUrl($this->options['authorization_url'], $params);
    }

}
