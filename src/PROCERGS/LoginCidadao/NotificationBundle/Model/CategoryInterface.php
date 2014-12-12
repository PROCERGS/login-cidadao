<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Model;

use PROCERGS\OAuthBundle\Model\ClientInterface;

interface CategoryInterface
{

    /**
     * Check if category is emailable
     *
     * @return boolean
     */
    public function isEmailable();

    /**
     * @return ClientInterface
     */
    public function getClient();
}
