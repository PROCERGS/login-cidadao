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
            'required' => false
        ));
        $builder->add('dateini', 'birthday', array(
            'required' => false,
            'format' => 'dd MMMM yyyy',
            'widget' => 'choice',
            'years' => range(date('Y'), date('Y') - 70)
        ));
        $builder->add('dateend', 'birthday', array(
            'required' => false,
            'format' => 'dd MMMM yyyy',
            'widget' => 'choice',
            'years' => range(date('Y'), date('Y') - 70)
        ));
        $builder->add('text', 'text', array(
            'required' => false
        ));
    }

    public function getName()
    {
        return 'suggestion_filter_form_type';
    }
}
