<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Validator\Constraints;

use LoginCidadao\OpenIDBundle\Validator\Constraints\SectorIdentifierUri;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class SectorIdentifierUriTest extends TestCase
{

    public function testAll()
    {
        $sectorIdUri = new SectorIdentifierUri();
        $this->assertSame('sector_identifier_uri', $sectorIdUri->validatedBy());
        $this->assertSame(Constraint::CLASS_CONSTRAINT, $sectorIdUri->getTargets());
    }
}
