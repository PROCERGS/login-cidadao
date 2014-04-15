<?php

namespace PROCERGS\LoginCidadao\CoreBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitterResourceOwner as Base;

/**
 * TwitterResourceOwner
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
