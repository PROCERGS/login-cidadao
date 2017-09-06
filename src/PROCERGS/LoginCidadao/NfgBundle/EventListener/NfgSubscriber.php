<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use PROCERGS\LoginCidadao\NfgBundle\Event\GetConnectCallbackResponseEvent;
use PROCERGS\LoginCidadao\NfgBundle\NfgEvents;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NfgSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /** @var EntityManagerInterface */
    private $em;

    /**
     * NfgSubscriber constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

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
            NfgEvents::CONNECT_CALLBACK_RESPONSE => 'onConnectCallbackResponse',
        ];
    }

    /**
     * Complete missing information with data from NFG.
     *
     * @param GetConnectCallbackResponseEvent $event
     */
    public function onConnectCallbackResponse(GetConnectCallbackResponseEvent $event)
    {
        $nfgProfile = $event->getPersonMeuRS()->getNfgProfile();
        $person = $event->getPersonMeuRS()->getPerson();

        if (!$person || !$nfgProfile) {
            return;
        }

        $updated = false;
        if (!$person->getMobile()) {
            if ($this->logger) {
                $this->logger->notice('Updating user\'s phone number with data from NFG',
                    ['person_id' => $person->getId()]);
            }
            $person->setMobile($nfgProfile->getMobile());
            $updated = true;
        }
        if (!$person->getBirthdate()) {
            if ($this->logger) {
                $this->logger->notice('Updating user\'s birthday with data from NFG',
                    ['person_id' => $person->getId()]);
            }
            $person->setBirthdate($nfgProfile->getBirthdate());
            $updated = true;
        }

        if ($updated) {
            $this->em->persist($person);
            $this->em->flush();
        }
    }
}
