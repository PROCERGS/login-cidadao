<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Model;

use PROCERGS\OAuthBundle\Model\ClientInterface;

interface CategoryInterface
{

    /**
     * @return ClientInterface
     */
    public function getClient();
}
