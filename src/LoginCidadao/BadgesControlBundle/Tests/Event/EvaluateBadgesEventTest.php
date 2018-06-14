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
use LoginCidadao\CoreBundle\Entity\Person;
use PHPUnit\Framework\TestCase;

class EvaluateBadgesEventTest extends TestCase
{
    public function testEvent()
    {
        $person = new Person();
        $this->assertEmpty($person->getBadges());

        $badge1 = $this->createMock('LoginCidadao\BadgesControlBundle\Model\BadgeInterface');
        $badge2 = $this->createMock('LoginCidadao\BadgesControlBundle\Model\BadgeInterface');

        $event = new EvaluateBadgesEvent($person);
        $event->registerBadges([$badge1, $badge2]);

        $this->assertSame($person, $event->getPerson());
        $this->assertCount(2, $person->getBadges());
    }
}
