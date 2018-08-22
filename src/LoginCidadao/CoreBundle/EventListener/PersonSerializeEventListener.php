<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\EventListener;

use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class PersonSerializeEventListener implements EventSubscriberInterface
{
    /** @var Packages */
    protected $templateHelper;

    /** @var UploaderHelper */
    protected $uploaderHelper;

    /** @var Kernel */
    protected $kernel;

    /** @var Request */
    protected $request;

    public function __construct(
        UploaderHelper $uploaderHelper,
        Packages $templateHelper,
        Kernel $kernel,
        RequestStack $requestStack
    ) {
        $this->uploaderHelper = $uploaderHelper;
        $this->templateHelper = $templateHelper;
        $this->kernel = $kernel;
        $this->request = $requestStack->getCurrentRequest();
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.pre_serialize',
                'method' => 'onPreSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
        ];
    }

    public function onPreSerialize(PreSerializeEvent $event)
    {
        $person = $event->getObject();
        if ($person instanceof PersonInterface) {
            $this->preparePictureUri($person);
        }
    }

    private function preparePictureUri(PersonInterface $person)
    {
        if ($this->hasLocalProfilePicture($person)) {
            $picturePath = $this->uploaderHelper->asset($person, 'image');
            $pictureUrl = $this->request->getUriForPath($picturePath);
        } elseif ($this->hasSocialNetworkPicture($person)) {
            $pictureUrl = $person->getSocialNetworksPicture();
        } else {
            $picturePath = $this->templateHelper->getUrl('bundles/logincidadaocore/images/userav.png');
            $pictureUrl = $this->request->getUriForPath($picturePath);
        }

        if ($this->kernel->getEnvironment() === 'dev') {
            $pictureUrl = str_replace('/app_dev.php', '', $pictureUrl);
        }

        $person->setProfilePictureUrl($pictureUrl);
        $person->serialize();
    }

    private function hasLocalProfilePicture(PersonInterface $person)
    {
        return !is_null($person->getImageName());
    }

    private function hasSocialNetworkPicture(PersonInterface $person)
    {
        // Currently only Facebook is supported
        return !is_null($person->getFacebookId());
    }
}
