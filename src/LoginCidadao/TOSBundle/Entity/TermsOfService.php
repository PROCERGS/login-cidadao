<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TOSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LoginCidadao\TOSBundle\Model\TOSInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * TermsOfService
 *
 * @ORM\Table(name="terms_of_service")
 * @ORM\Entity(repositoryClass="LoginCidadao\TOSBundle\Entity\TermsOfServiceRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"createdAt"})
 */
class TermsOfService implements TOSInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="Symfony\Component\Security\Core\User\UserInterface")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="terms", type="text")
     */
    private $terms;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="final", type="boolean")
     */
    private $final;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set author
     *
     * @param UserInterface $author
     * @return TermsOfService
     */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     * 
     * @return UserInterface
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Get terms of service text
     * 
     * @return string
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * Set terms of service text
     * 
     * @param string $terms
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return TermsOfService
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if (!($this->getCreatedAt() instanceof \DateTime)) {
            $this->createdAt = new \DateTime();
        }
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return TermsOfService
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set final
     *
     * @param boolean $final
     * @return TermsOfService
     */
    public function setFinal($final = true)
    {
        $this->final = $final;

        return $this;
    }

    /**
     * Get final
     *
     * @return boolean 
     */
    public function getFinal()
    {
        return $this->final;
    }

    public function isFinal()
    {
        return $this->getFinal();
    }
}
