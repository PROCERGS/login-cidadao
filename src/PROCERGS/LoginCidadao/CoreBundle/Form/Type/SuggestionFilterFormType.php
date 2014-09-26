<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

class SuggestionFilterFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text', array(
            'required' => false,
            'label' => 'sugg.username'
        ));
        $builder->add('dateini', 'birthday', array(
            'required' => false,
            'format' => 'dd MMMM yyyy',
            'widget' => 'choice',
            'years' => range(date('Y'), 1898),
            'label' => 'sugg.dateini'
        ));
        $builder->add('dateend', 'birthday', array(
            'required' => false,
            'format' => 'dd MMMM yyyy',
            'widget' => 'choice',
            'years' => range(date('Y'), 1898),
            'label' => 'sugg.dateend'
        ));
        $builder->add('text', 'text', array(
            'required' => false,
            'label' => 'sugg.text'
        ));
        $builder->setMethod('GET');
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'sugg_filt_form_type';
    }
}
