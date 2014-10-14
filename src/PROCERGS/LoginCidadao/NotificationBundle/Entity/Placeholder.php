<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Category;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * Placeholder
 *
 * @ORM\Table(name="placeholder")
 * @ORM\Entity
 * @UniqueEntity(fields={"category", "name"},errorPath="name")
 */
class Placeholder
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max = "255")
     * @Assert\NotEqualTo(value="title")
     * @Assert\NotEqualTo(value="shortext")
     * @Assert\NotEqualTo(value="icon")
     */
    private $name;

    /**
     * @var string
     * @Assert\Length(max = "255")
     * @ORM\Column(name="defaultValue", type="string", length=255, nullable=true)
     */
    private $default;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="placeholders")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

}
