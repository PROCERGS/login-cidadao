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

use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\CoreBundle\Event\TranslateScopeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ScopeTranslatorSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            LoginCidadaoCoreEvents::TRANSLATE_SCOPE => 'onTranslateScope',
        ];
    }

    public function onTranslateScope(TranslateScopeEvent $event)
    {
        // The following scopes have no translation since they are implementation details the user don't care about
        switch ($event->getScope()) {
            case 'openid':
            case 'offline_access':
                $event->setTranslation('');
                break;
            default:
                break;
        }
    }
}
