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
use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Event\GetClientEvent;
use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Event\AuthorizationEvent;
use LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDEvents;
use LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OAuthEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION => ['onNewAuthorization', 10],
            LoginCidadaoOpenIDEvents::UPDATE_AUTHORIZATION => ['onUpdateAuthorization', 10],
        ];
    }

    public function onNewAuthorization(AuthorizationEvent $event)
    {
        $authorization = new Authorization();
        $authorization->setPerson($event->getPerson());
        $authorization->setClient($event->getClient());
        $authorization->setScope($event->getScope());

        $event->setAuthorization($authorization);
    }

    public function onUpdateAuthorization(AuthorizationEvent $event)
    {
        $authorization = $event->getAuthorization();
        if (!$authorization instanceof Authorization) {
            // A pre-existing Authorization is expected. There is nothing we can do here.
            return;
        }
        $merged = array_merge($authorization->getScope(), $event->getScope());
        $authorization->setScope($merged);
    }
}
