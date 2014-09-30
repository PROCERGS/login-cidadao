<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;

use Doctrine\ORM\Mapping as ORM;
use PROCERGS\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Notification
 *
 * @ORM\Table(name="shout")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Shout
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\ShoutPerson", mappedBy="shout") 
     */
    private $receivers;

    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Person", inversedBy="shouts")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Category", inversedBy="shouts")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @ORM\Column(name="html_tpl", type="text", nullable=true)
     */
    private $htmlTpl;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if (! ($this->getCreatedAt() instanceof \DateTime)) {
            $this->createdAt = new \DateTime();
        }
    }
}
