<?php

namespace PROCERGS\LoginCidadao\BadgesBundle\Model;

interface BadgeInterface
{

    public function getNamespace();

    public function getName();

    public function getData();
}
