<?php

namespace PROCERGS\LoginCidadao\APIBundle\Security\Audit\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Loggable extends ConfigurationAnnotation
{

    const TYPE_SELECT = 'SELECT';
    const TYPE_UPDATE = 'UPDATE';
    const TYPE_CREATE = 'CREATE';
    const TYPE_DELETE = 'DELETE';

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
            case self::TYPE_CREATE:
            case self::TYPE_DELETE:
            case self::TYPE_SELECT:
            case self::TYPE_UPDATE:
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
