<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

use Doctrine\ORM\EntityManager;

class BackgroundImageHelper
{

    /**
     *
     * @var EntityManager
     */
    private $em;
    public $backgroundImage;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->setRandomImage();
    }

    private function getRepository()
    {
        return $this->em->getRepository("PROCERGSLoginCidadaoCoreBundle:BackgroundImage");
    }

    public function setRandomImage()
    {
        $all = $this->getRepository()->findAll();
        shuffle($all);
        $this->backgroundImage = reset($all);
    }

}
