<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\BadgesControlBundle\Tests\Event;

use LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent;
use LoginCidadao\BadgesControlBundle\Tests\BadgeMocker;
use LoginCidadao\CoreBundle\Entity\Person;
use PHPUnit\Framework\TestCase;

class EvaluateBadgesEventTest extends TestCase
{
    public function testEvent()
    {
        $person = new Person();
        $this->assertEmpty($person->getBadges());

        $badge1 = BadgeMocker::getBadge('namespace1', 'badge1');
        $badge2 = BadgeMocker::getBadge('namespace1', 'badge2');
        $badge3 = BadgeMocker::getBadge('namespace2', 'badge1');

        $event = new EvaluateBadgesEvent($person);
        $event->registerBadges([$badge1, $badge2, $badge3]);

        $this->assertSame($person, $event->getPerson());
        $this->assertCount(3, $person->getBadges());
    }
}
