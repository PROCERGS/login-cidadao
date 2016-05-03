<?php

namespace LoginCidadao\CoreBundle\Model;

/**
 * Defines an Interface for key entities in the application that should be
 * uniquely identifiable without depending on auto-incremented IDs.
 */
interface UniqueEntityInterface
{

    /**
     * Gets the Unique Id of the Entity.
     * @return string the entity UID
     */
    public function getUid();

    /**
     * Sets the Unique Id of the Entity.
     * @param string $uid the entity UID
     * @return AbstractUniqueEntity
     */
    public function setUid($uid = null);
}
