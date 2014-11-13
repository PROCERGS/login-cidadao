<?php

namespace PROCERGS\LoginCidadao\BadgesControlBundle\Model;

interface BadgeEvaluatorInterface
{

    public function getName();

    public function getAvailableBadges();
}
