<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\EventListener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Serializer;
use LoginCidadao\OAuthBundle\Model\AccessTokenManager;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;

class PersonSerializeEventListenner implements EventSubscriberInterface
{
    /** @var MeuRSHelper */
    protected $meuRSHelper;

    /** @var Serializer */
    protected $serializer;

    /** @var string */
    protected $pairwiseSubjectIdSalt;

    /**
     * PersonSerializeEventListenner constructor.
     * @param MeuRSHelper $meuRSHelper
     * @param Serializer $serializer
     */
    public function __construct(MeuRSHelper $meuRSHelper, Serializer $serializer)
    {
        $this->meuRSHelper = $meuRSHelper;
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ),
        );
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        if (!($event->getObject() instanceof PersonInterface)) {
            return;
        }
        $this->setVoterRegistration($event);
    }

    private function setVoterRegistration(ObjectEvent $event)
    {
        $personMeuRS = $this->meuRSHelper->getPersonMeuRS($event->getObject(), false);

        if (!($personMeuRS instanceof PersonMeuRS)) {
            return;
        }

        $groups = $event->getContext()->attributes->get('groups')->get();
        if (array_search('voter_registration', $groups) !== false) {
            $event->getVisitor()->addData('voter_registration', $personMeuRS->getVoterRegistration());
        }
    }
}
