<?php
namespace PROCERGS\LoginCidadao\IgpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="igp_id_card")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks 
 */
class IgpIdCard
{

    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\IdCard")
     * @ORM\JoinColumn(name="id_card_id", referencedColumnName="id")
     */
    protected $idCard;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    protected $id;

    /**
     * @JMS\Expose
     * @JMS\Groups({"id_cards"})
     * @Assert\Length(min=1,max="66")
     * @ORM\Column(name="nome_mae", type="string", length=66, nullable=true)
     * @RG     
     */
    protected $nomeMae;

    /**
     * @JMS\Expose
     * @JMS\Groups({"id_cards"})
     * @ORM\Column(name="data_emissao_ci", type="date", nullable=true)
     */
    protected $dataEmissaoCI;

    /**
     * @JMS\Expose
     * @JMS\Groups({"id_cards"})
     * @Assert\Length(min=1,max="66")
     * @ORM\Column(name="nome_ci", type="string", length=66, nullable=true)
     */
    protected $nomeCI;

    /**
     * @JMS\Expose
     * @JMS\Groups({"id_cards"})
     * @Assert\Length(min=1,max="10")
     * @ORM\Column(name="rg", type="string", length=10, nullable=true)
     */
    protected $rg;

    /**
     * @JMS\Expose
     * @JMS\Groups({"id_cards"})
     * @Assert\Length(min=1,max="11")
     * @ORM\Column(name="cpf", type="string", length=11, nullable=true)
     */
    protected $cpf;

    /**
     * @JMS\Expose
     * @JMS\Groups({"id_cards"})
     * @Assert\Length(min=1,max="1")
     * @ORM\Column(name="situacao_rg", type="string", length=1, nullable=true)
     */
    protected $situacaoRg;

    public function setId($var)
    {
        $this->id = $var;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setIdCard($var)
    {
        $this->idCard = $var;
        return $this;
    }

    public function getIdCard()
    {
        return $this->idCard;
    }

    public function setNomeMae($var)
    {
        $this->nomeMae = $var;
        return $this;
    }

    public function getNomeMae()
    {
        return $this->nomeMae;
    }

    public function setDataEmissaoCI($var)
    {
        $this->dataEmissaoCI = $var;
        return $this;
    }

    public function getDataEmissaoCI()
    {
        return $this->dataEmissaoCI;
    }

    public function setNomeCI($var)
    {
        $this->nomeCI = $var;
        return $this;
    }

    public function getNomeCI()
    {
        return $this->nomeCI;
    }

    public function setRg($var)
    {
        $this->rg = $var;
        return $this;
    }

    public function getRg()
    {
        return $this->rg;
    }

    public function setCpf($var)
    {
        $this->cpf = $var;
        return $this;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function setSituacaoRg($var)
    {
        $this->situacaoRg = $var;
        return $this;
    }

    public function getSituacaoRg()
    {
        return $this->situacaoRg;
    }
    
}