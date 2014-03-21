<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Entity\BackgroundImage;

class BackgroundImageHelper
{

    /**
     *
     * @var EntityManager
     */
    private $em;
    private $default;
    public $backgroundImage;

    public function __construct(EntityManager $em, $author, $url, $file)
    {
        $this->em = $em;
        $this->default = array('author' => $author, 'url' => $url, 'file' => $file);
        $this->setRandomImage();
    }

    private function getRepository()
    {
        return $this->em->getRepository("PROCERGSLoginCidadaoCoreBundle:BackgroundImage");
    }

    public function setRandomImage()
    {
        $all = $this->getRepository()->findAll();
        if (!empty($all)) {
            shuffle($all);
            $this->backgroundImage = reset($all);
        } else {
            $image = new BackgroundImage();
            $image->setAuthor($this->default['author']);
            $image->setFile($this->default['file']);
            $image->setUrl($this->default['url']);

            $this->backgroundImage = $image;
        }
    }

}
