<?php

namespace LoginCidadao\BadgesControlBundle\Model;

interface BadgeEvaluatorInterface
{

    public function getName();

    public function getAvailableBadges();
}
