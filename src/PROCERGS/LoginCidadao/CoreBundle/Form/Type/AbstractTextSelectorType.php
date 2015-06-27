<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

abstract class AbstractTextSelectorType extends AbstractType
{

    /** @var EntityManager */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getParent()
    {
        return 'text';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = $this->getTransformer();
        $builder->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'The selected value was not found'
        ));
    }


    /**
     * @return DataTransformerInterface
     */
    public abstract function getTransformer();

}
