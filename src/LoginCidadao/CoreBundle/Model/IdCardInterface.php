<?php

namespace LoginCidadao\CoreBundle\Model;

use LoginCidadao\CoreBundle\Entity\State;

interface IdCardInterface
{

    public function getId();

    public function setId($id);

    public function setState(State $state);

    public function setPerson(PersonInterface $person);

    /**
     * @return PersonInterface
     */
    public function getPerson();

    /**
     * @return State
     */
    public function getState();

    public function setValue($value);

    public function getValue();

    public function setIssuer($issuer);

    public function getIssuer();
}
