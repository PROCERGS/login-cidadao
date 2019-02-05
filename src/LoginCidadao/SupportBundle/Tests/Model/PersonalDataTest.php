<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\SupportBundle\Tests\Model;

use LoginCidadao\SupportBundle\Model\PersonalData;
use PHPUnit\Framework\TestCase;

class PersonalDataTest extends TestCase
{
    public function testKnownValue()
    {
        $data = PersonalData::createWithValue($name = 'privateInfo', $value = 'my secret value');

        $this->assertSame($name, $data->getName());
        $this->assertSame($value, $data->getValue());
        $this->assertNotNull($data->getHash());
        $this->assertNotNull($data->getChallenge());
        $this->assertTrue($data->checkValue($value));
    }

    public function testUnknownValue()
    {
        $value = 'my unknown secret value';
        $data = PersonalData::createWithoutValue($name = 'privateInfo', $value);

        $this->assertSame($name, $data->getName());
        $this->assertNull($data->getValue());
        $this->assertNotNull($data->getHash());
        $this->assertNotNull($data->getChallenge());
        $this->assertTrue($data->checkValue($value));
    }

    public function testCreateInvalidObject()
    {
        $this->expectException(\InvalidArgumentException::class);

        new PersonalData('invalid', null, true, null);
    }
}
