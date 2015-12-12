<?php

namespace LoginCidadao\NotificationBundle\Model;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\NotificationBundle\Model\CategoryInterface;

interface NotificationInterface
{

    const LEVEL_NORMAL = 1;
    const LEVEL_IMPORTANT = 2;
    const LEVEL_EXTREME = 3;

    public function getId();

    public function getIcon();

    public function setIcon($icon);

    public function getTitle();

    public function setTitle($title);

    public function getShortText();

    public function setShortText($shortText);

    public function getText();

    public function setText($text);

    /**
     * @return PersonInterface
     */
    public function getPerson();

    public function setPerson($person);

    public function getCreatedAt();

    public function wasRead();

    public function getRead();

    public function getReadDate();

    public function setRead($seen);

    /**
     * Checks if the receiver of the notification has authorized the sender app.
     * @return boolean
     */
    public function checkReceiver();

    public function checkSender();

    public function canBeSent();

    public function getSender();

    /**
     * Returns the severity of the notification
     */
    public function getLevel();

    public function isExtreme();

    public function setLevel($level);

    public function isGlyphicon();

    /** @var CategoryInterface */
    public function getCategory();

    public function setPlaceholders($var);

    public function getPlaceholders();

    public function setMailTemplate($var);

    public function getMailTemplate();

    public function getHtmlTemplate();

    public function isRead();
}
