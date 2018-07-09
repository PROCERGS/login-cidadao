<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\EventListener;

use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\CoreBundle\Event\TranslateScopeEvent;
use LoginCidadao\OpenIDBundle\EventListener\ScopeTranslatorSubscriber;
use PHPUnit\Framework\TestCase;

class ScopeTranslatorSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            LoginCidadaoCoreEvents::TRANSLATE_SCOPE => 'onTranslateScope',
        ], ScopeTranslatorSubscriber::getSubscribedEvents());
    }

    public function testOnTranslateInternalScope()
    {
        $subscriber = new ScopeTranslatorSubscriber();

        $event = new TranslateScopeEvent('openid');
        $subscriber->onTranslateScope($event);
        $this->assertSame('', $event->getTranslation());

        $event = new TranslateScopeEvent('offline_access');
        $subscriber->onTranslateScope($event);
        $this->assertSame('', $event->getTranslation());
    }

    public function testOnTranslateNonInternalScope()
    {
        $subscriber = new ScopeTranslatorSubscriber();

        $event = new TranslateScopeEvent('name');
        $subscriber->onTranslateScope($event);
        $this->assertNull($event->getTranslation());
    }
}
