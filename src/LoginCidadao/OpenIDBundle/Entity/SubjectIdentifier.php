<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="LoginCidadao\OpenIDBundle\Entity\SubjectIdentifierRepository")
 * @UniqueEntity({"client", "person"})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="subject_identifier")
 */
class SubjectIdentifier
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var PersonInterface
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     */
    private $person;

    /**
     * @var ClientInterface
     * @ORM\ManyToOne(targetEntity="LoginCidadao\OAuthBundle\Entity\Client", inversedBy="authorizations")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    private $client;

    /**
     * @var mixed
     * @ORM\Column(name="subject_identifier", type="string", nullable=false)
     */
    private $subjectIdentifier;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @return mixed
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param mixed $person
     * @return SubjectIdentifier
     */
    public function setPerson(PersonInterface $person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param ClientInterface $client
     * @return SubjectIdentifier
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubjectIdentifier()
    {
        return $this->subjectIdentifier;
    }

    /**
     * @param mixed $subjectIdentifier
     * @return SubjectIdentifier
     */
    public function setSubjectIdentifier($subjectIdentifier)
    {
        $this->subjectIdentifier = $subjectIdentifier;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist()
     * @param mixed $createdAt
     * @return SubjectIdentifier
     */
    public function setCreatedAt($createdAt = null)
    {
        if ($createdAt instanceof \DateTime) {
            $this->createdAt = $createdAt;
        } else {
            $this->createdAt = \DateTime::createFromFormat('U', time());
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * @param \DateTime $updatedAt
     * @return SubjectIdentifier
     */
    public function setUpdatedAt($updatedAt = null)
    {
        if ($updatedAt instanceof \DateTime) {
            $this->updatedAt = $updatedAt;
        } else {
            $this->updatedAt = \DateTime::createFromFormat('U', time());
        }

        return $this;
    }
}
