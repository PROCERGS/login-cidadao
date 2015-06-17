<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Model;

use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;

interface LocationAwareInterface
{

    public function getCity();

    public function setCity(City $city = null);

    public function getState();

    public function setState(State $state = null);

    public function getCountry();

    public function setCountry(Country $country = null);
}
