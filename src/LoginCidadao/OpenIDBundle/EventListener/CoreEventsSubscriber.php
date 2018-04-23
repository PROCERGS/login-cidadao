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

use LoginCidadao\CoreBundle\Event\GetClientEvent;
use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadataRepository;
use LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoreEventsSubscriber implements EventSubscriberInterface
{
    /** @var ClientMetadataRepository */
    protected $clientMetadataRepository;

    /** @var SectorIdentifierUriChecker */
    protected $sectorIdentifierUriChecker;

    /** @var boolean */
    protected $revalidateSectorIdentifierUriOnAuth;

    /**
     * CoreEventsListener constructor.
     * @param ClientMetadataRepository $clientMetadataRepository
     * @param SectorIdentifierUriChecker $sectorIdentifierUriChecker
     * @param boolean $revalidateSectorIdentifierUriOnAuth
     */
    public function __construct(
        ClientMetadataRepository $clientMetadataRepository,
        SectorIdentifierUriChecker $sectorIdentifierUriChecker,
        $revalidateSectorIdentifierUriOnAuth
    ) {
        $this->clientMetadataRepository = $clientMetadataRepository;
        $this->sectorIdentifierUriChecker = $sectorIdentifierUriChecker;
        $this->revalidateSectorIdentifierUriOnAuth = $revalidateSectorIdentifierUriOnAuth;
    }


    public static function getSubscribedEvents()
    {
        return [
            LoginCidadaoCoreEvents::GET_CLIENT => ['onGetClient', 10],
        ];
    }

    public function onGetClient(GetClientEvent $event)
    {
        if ($this->revalidateSectorIdentifierUriOnAuth === false) {
            return;
        }

        $metadata = $this->clientMetadataRepository->findOneBy(['client' => $event->getClient()]);

        if ($metadata instanceof ClientMetadata) {
            $this->sectorIdentifierUriChecker->recheck($metadata);
        }
    }
}
