<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\EventListener;

use Doctrine\ORM\EntityManager;
use LoginCidadao\CoreBundle\Event\GetClientEvent;
use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CoreEventsSubscriber implements EventSubscriberInterface
{
    /** @var EntityManager */
    protected $em;

    /** @var SectorIdentifierUriChecker */
    protected $sectorIdentifierUriChecker;

    /** @var boolean */
    protected $revalidateSectorIdentifierUriOnAuth;

    /**
     * CoreEventsListener constructor.
     * @param EntityManager $em
     * @param SectorIdentifierUriChecker $sectorIdentifierUriChecker
     * @param boolean $revalidateSectorIdentifierUriOnAuth
     */
    public function __construct(
        EntityManager $em,
        SectorIdentifierUriChecker $sectorIdentifierUriChecker,
        $revalidateSectorIdentifierUriOnAuth
    ) {
        $this->em = $em;
        $this->sectorIdentifierUriChecker = $sectorIdentifierUriChecker;
        $this->revalidateSectorIdentifierUriOnAuth = $revalidateSectorIdentifierUriOnAuth;
    }


    public static function getSubscribedEvents()
    {
        return array(
            LoginCidadaoCoreEvents::GET_CLIENT => array(
                array('onGetClient', 10),
            ),
            KernelEvents::EXCEPTION
        );
    }

    public function onGetClient(GetClientEvent $event)
    {
        if ($this->revalidateSectorIdentifierUriOnAuth === false) {
            return;
        }

        $repo = $this->em->getRepository('LoginCidadaoOpenIDBundle:ClientMetadata');
        $metadata = $repo->findOneBy(
            array(
                'client' => $event->getClient(),
            )
        );
        $this->sectorIdentifierUriChecker->recheck($metadata);
    }
}
