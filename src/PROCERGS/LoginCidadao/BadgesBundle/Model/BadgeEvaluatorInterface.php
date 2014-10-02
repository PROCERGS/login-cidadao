<?php

namespace PROCERGS\LoginCidadao\BadgesBundle\Model;

interface BadgeEvaluatorInterface
{

    public function getName();

    public function getAvailableBadges();
}
