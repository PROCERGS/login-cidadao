<?php

namespace LoginCidadao\TOSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LoginCidadao\TOSBundle\Model\TOSInterface;
use LoginCidadao\TOSBundle\Model\AgreementInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Agreement
 *
 * @ORM\Table(name="agreement")
 * @ORM\Entity(repositoryClass="LoginCidadao\TOSBundle\Entity\AgreementRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Agreement implements AgreementInterface
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
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var TOSInterface
     *
     * @ORM\ManyToOne(targetEntity="LoginCidadao\TOSBundle\Model\TOSInterface")
     * @ORM\JoinColumn(name="terms_of_service_id", referencedColumnName="id")
     */
    private $termsOfService;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="agreed_at", type="datetime")
     */
    private $agreedAt;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getTermsOfService()
    {
        return $this->termsOfService;
    }

    public function setUser(UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }

    public function setTermsOfService(TOSInterface $termsOfService)
    {
        $this->termsOfService = $termsOfService;
        return $this;
    }

    /**
     * Set agreedAt
     *
     * @param \DateTime $agreedAt
     * @return Agreement
     */
    public function setAgreedAt($agreedAt)
    {
        $this->agreedAt = $agreedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setAgreedAtValue()
    {
        if (!($this->getAgreedAt() instanceof \DateTime)) {
            $this->agreedAt = new \DateTime();
        }
    }

    /**
     * Get agreedAt
     *
     * @return \DateTime 
     */
    public function getAgreedAt()
    {
        return $this->agreedAt;
    }
}
