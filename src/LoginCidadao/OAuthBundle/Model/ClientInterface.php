<?php

namespace LoginCidadao\OAuthBundle\Model;

use FOS\OAuthServerBundle\Model\ClientInterface as BaseInterface;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;

interface ClientInterface extends BaseInterface
{
    public function getId();

    public function setName($name);

    public function getName();

    public function setDescription($description);

    public function getDescription();

    public function setSiteUrl($url);

    public function getSiteUrl();

    public function getAuthorizations();

    public function removeAuthorization(Authorization $authorization);

    public function getLandingPageUrl();

    public function setLandingPageUrl($landingPageUrl);

    public function getTermsOfUseUrl();

    public function setTermsOfUseUrl($termsOfUseUrl);

    public function isVisible();

    public function setVisible($visible);

    public function isPublished();

    public function setPublished($published);

    public function setId($var);

    public function getCategories();

    public function getOwners();

    public function setOwners(ArrayCollection $owners);

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImage($image);

    /**
     * @return File
     */
    public function getImage();

    /**
     * @param string $imageName
     */
    public function setImageName($imageName);

    /**
     * @return string
     */
    public function getImageName();

    /**
     * @return ClientMetadata
     */
    public function getMetadata();
}
