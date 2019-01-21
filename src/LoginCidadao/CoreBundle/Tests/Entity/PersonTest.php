<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Entity;

use LoginCidadao\BadgesControlBundle\Tests\BadgeMocker;
use LoginCidadao\CoreBundle\Entity\Person;
use PHPUnit\Framework\TestCase;

class PersonTest extends TestCase
{
    public function testBadges()
    {
        $badge1 = BadgeMocker::getBadge('namespace1', 'badge1');
        $badge2 = BadgeMocker::getBadge('namespace1', 'badge2');
        $badge3 = BadgeMocker::getBadge('namespace2', 'badge1');

        $person = new Person();
        $person->mergeBadges([$badge1]);
        $person->mergeBadges([$badge2, $badge3]);

        $badges = $person->getBadges();

        $this->assertCount(3, $badges);
        $this->assertContains('namespace1.badge1', array_keys($badges));
        $this->assertContains('namespace1.badge2', array_keys($badges));
        $this->assertContains('namespace2.badge1', array_keys($badges));

        $this->assertSame($badge1, $badges['namespace1.badge1']);
        $this->assertSame($badge2, $badges['namespace1.badge2']);
        $this->assertSame($badge3, $badges['namespace2.badge1']);
    }

    public function testEmptyBadges()
    {
        $person = new Person();

        $this->assertEmpty($person->getBadges());
    }
}
