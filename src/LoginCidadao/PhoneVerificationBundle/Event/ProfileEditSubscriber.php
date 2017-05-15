<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Event;


use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProfileEditSubscriber implements EventSubscriberInterface
{
    private $originalPhone = null;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::PROFILE_EDIT_INITIALIZE => 'onProfileEditInitialize',
            FOSUserEvents::PROFILE_EDIT_SUCCESS => 'onProfileEditSuccess',
        ];
    }

    public function onProfileEditInitialize(GetResponseUserEvent $event)
    {
        $person = $event->getUser();

        if (!($person instanceof PersonInterface)) {
            return;
        }

        $this->originalPhone = $person->getMobile();
    }

    public function onProfileEditSuccess(FormEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $person = $event->getForm()->getData();
        if (!($person instanceof PersonInterface)) {
            return;
        }

        if ($this->originalPhone != $person->getMobile()) {
            $phoneChangedEvent = new PhoneChangedEvent($person, $this->originalPhone);
            $dispatcher->dispatch(PhoneVerificationEvents::PHONE_CHANGED, $phoneChangedEvent);
        }
    }
}
