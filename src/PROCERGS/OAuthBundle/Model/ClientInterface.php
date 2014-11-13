<?php

namespace PROCERGS\OAuthBundle\Model;

use FOS\OAuthServerBundle\Model\ClientInterface as BaseInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;

interface ClientInterface extends BaseInterface
{

    public function setName($name);

    public function getName();

    public function setDescription($description);

    public function getDescription();

    public function setSiteUrl($url);

    public function getSiteUrl();

    public function getAuthorizations();

    public function removeAuthorization(Authorization $authorization);

    public function getMaxNotificationLevel();

    public function setMaxNotificationLevel($maxNotificationLevel);

    public function getLandingPageUrl();

    public function setLandingPageUrl($landingPageUrl);

    public function getTermsOfUseUrl();

    public function setTermsOfUseUrl($termsOfUseUrl);

    public function getAbsolutePicturePath();

    public function getPictureWebPath();

    public function setPictureFile(File $pictureFile = null);

    public function getPictureFile();

    public function isVisible();

    public function setVisible($visible);

    public function isPublished();

    public function setPublished($published);

    public function setId($var);

    public function getCategories();

    public function getOwners();

    public function setOwners(ArrayCollection $owners);
}
