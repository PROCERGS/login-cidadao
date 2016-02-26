<?php
namespace LoginCidadao\OAuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Notification
 *
 * @ ORM\Table(name="client_person")
 * @ ORM\Entity
 * @ UniqueEntity(fields={"person", "client"},errorPath="person")
 */
class ClientPerson
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person", inversedBy="clients")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected $person;

    protected $client;

    public function getId()
    {
        return $this->id;
    }

    public function setId($var)
    {
        $this->id = $var;
        return $this;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function setPerson($var)
    {
        $this->person = $var;
        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient($var)
    {
        $this->client = $var;
        return $this;
    }
}
