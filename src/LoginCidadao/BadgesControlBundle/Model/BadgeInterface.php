<?php

namespace LoginCidadao\BadgesControlBundle\Model;

interface BadgeInterface
{

    public function getNamespace();

    public function getName();

    public function getData();
}
