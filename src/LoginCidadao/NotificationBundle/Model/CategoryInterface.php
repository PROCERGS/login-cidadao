<?php

namespace LoginCidadao\NotificationBundle\Model;

use LoginCidadao\OAuthBundle\Model\ClientInterface;

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
