<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PROCERGS\LoginCidadao\CoreBundle\Form\DataTransformer\FromArray;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;
class ClientBaseFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'required' => true
        ));
        $builder->add('description', 'textarea', array(
            'required' => true,
            'attr' => array('rows' => 4)
        ));
        $builder->add('siteUrl', 'text', array(
            'required' => true
        ));
        $builder->add($builder->create('redirectUris', 'textarea', array(
            'required' => true,
            'attr' => array('rows' => 4)
        ))->addModelTransformer(new FromArray()) );
        $builder->add('landingPageUrl', 'text', array(
            'required' => true
        ));
        $builder->add('termsOfUseUrl', 'text', array(
            'required' => true
        ));
                
        
        $builder->add('pictureFile');
        $builder->add('id', 'hidden', array(
            'required' => false
        ));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entity = $event->getData();
            $form = $event->getForm();
            if ($entity->getId()) {
                $form->add('publicId', 'textarea', array(
                    'required' => false,
                    'read_only' => true,
                    'attr' => array('rows' => 4)
                ));
                $form->add('secret', 'textarea', array(
                    'required' => false,
                    'read_only' => true,
                    'attr' => array('rows' => 4)
                ));
                $form->add('persons', 'ajax_choice', array(
                    'ajax_choice_attr' => array(
                        'filter' => array(
                            'route' => 'lc_dev_client_grid_developer_filter',
                            'search_prop' => 'username',
                            'extra_form_prop' => array('service_id' => 'id')
                        ),
                        'selected' => array(
                            'route' => 'lc_dev_client_grid_developer',
                            'extra_form_prop' => array('person_id' => 'persons')
                        ),
                        'property_value' => 'id',
                        'property_text' => 'fullNameOrUsername',
                    ),
                    'required' => true,
                    'class' => 'PROCERGSLoginCidadaoCoreBundle:Person',
                    'property' => 'fullName',
                    'query_builder' => function(EntityRepository $er) use (&$entity) {
                        //$country = $er->createQueryBuilder('h')->getEntityManager()->getRepository('PROCERGSLoginCidadaoCoreBundle:Person')->findOneBy(array('iso2' => 'BR'));
                        return $er->createQueryBuilder('u')
                        ->join('PROCERGSOAuthBundle:ClientPerson', 'cp', 'with', 'cp.person = u')
                        ->where('cp.client = :client')->setParameter('client', $entity)
                        ->orderBy('u.username', 'ASC');
                    }
                ));      
            } else {
                $form->add('publicId', 'hidden', array(
                    'required' => false,
                    'mapped' =>false
                ));
                $form->add('secret', 'hidden', array(
                    'required' => false,
                    'mapped' =>false
                ));
            }
        });
        $builder->add('published', 'switch', array(
            'required' => false
        ));
        $builder->add('visible', 'switch', array(
            'required' => false
        ));
    }

    public function getName()
    {
        return 'client_base_form_type';
    }
}
