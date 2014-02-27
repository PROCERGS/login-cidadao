<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

interface NotificationInterface
{

    public function getIcon();
    public function setIcon($icon);

    public function getTitle();
    public function setTitle($title);

    public function getShortText();
    public function setShortText($shortText);

    public function getText();
    public function setText($text);

    public function getClient();
    public function setClient($client);

    public function getPerson();
    public function setPerson($person);

    public function getCreatedAt();

    public function wasRead();
    public function getRead();
    public function getReadDate();
    public function setRead($seen);

}
