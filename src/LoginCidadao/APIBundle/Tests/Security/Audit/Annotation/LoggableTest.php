<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\APIBundle\Tests\Security\Audit\Annotation;

use LoginCidadao\APIBundle\Entity\ActionLog;
use LoginCidadao\APIBundle\Security\Audit\Annotation\Loggable;
use PHPUnit\Framework\TestCase;

class LoggableTest extends TestCase
{

    public function testActionLogId()
    {
        $id = 'actionLogId';
        $loggable = new Loggable([]);
        $loggable->setActionLogId($id);

        $this->assertSame($id, $loggable->getActionLogId());
    }

    public function testType()
    {
        $loggable = new Loggable(['type' => ActionLog::TYPE_CREATE]);
        $this->assertSame(ActionLog::TYPE_CREATE, $loggable->getType());

        $validTypes = [
            ActionLog::TYPE_CREATE,
            ActionLog::TYPE_DELETE,
            ActionLog::TYPE_SELECT,
            ActionLog::TYPE_UPDATE,
            ActionLog::TYPE_LOGIN,
        ];

        foreach ($validTypes as $type) {
            $loggable->setType($type);
            $this->assertSame($type, $loggable->getType());
        }

        $loggable->setType('InvalidType');
        $this->assertSame('UNKNOWN', $loggable->getType());
    }

    public function testAllowArray()
    {
        $loggable = new Loggable([]);
        $this->assertTrue($loggable->allowArray());
    }

    public function testGetAliasName()
    {
        $loggable = new Loggable([]);
        $this->assertSame('loggable', $loggable->getAliasName());
    }
}
