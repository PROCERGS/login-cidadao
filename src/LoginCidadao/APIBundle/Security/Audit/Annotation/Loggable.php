<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\APIBundle\Security\Audit\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;
use LoginCidadao\APIBundle\Entity\ActionLog;

/**
 * @Annotation
 */
class Loggable extends ConfigurationAnnotation
{

    protected $type;
    private $actionLogId;

    public function allowArray()
    {
        return true;
    }

    public function getAliasName()
    {
        return "loggable";
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this->type;
    }

    public function getType()
    {
        switch ($this->type) {
            case ActionLog::TYPE_CREATE:
            case ActionLog::TYPE_DELETE:
            case ActionLog::TYPE_SELECT:
            case ActionLog::TYPE_UPDATE:
            case ActionLog::TYPE_LOGIN:
                return $this->type;
            default:
                return "UNKNOWN";
        }
    }

    public function setActionLogId($id)
    {
        $this->actionLogId = $id;

        return $this;
    }

    public function getActionLogId()
    {
        return $this->actionLogId;
    }

}
