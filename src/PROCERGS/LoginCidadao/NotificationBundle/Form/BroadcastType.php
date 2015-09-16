<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\CategoryRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class BroadcastType extends AbstractType
{
    private $person;
    private $clientId;

    public function __construct(PersonInterface $person, $clientId)
    {
        $this->person   = $person;
        $this->clientId = $clientId;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $person          = $this->person;
        $clientId        = $this->clientId;
        $receiversConfig = array(
            'label' => 'Receivers',
            'ajax_choice_attr' => array(
                'filter' => array(
                    'route' => 'lc_dev_broadcasts_grid_receivers_filter',
                    'search_prop' => 'username',
                    'extra_form_prop' => array('client_id' => 'client_id')
                ),
                'selected' => array(
                    'route' => 'lc_dev_broadcasts_grid_receivers',
                    'extra_form_prop' => array('person_id' => 'receivers', 'client_id' => 'client_id')
                ),
                'property_value' => 'id',
                'property_text' => 'fullNameOrUsername',
                'search_prop_label' => 'dev.broadcasts.receivers.filter'
            ),
            'required' => true,
            'empty_data' => null,
            'class' => 'PROCERGSLoginCidadaoCoreBundle:Person',
            'property' => 'fullNameOrUsername'
        );
        $builder->add('category', 'entity',
            array(
            'required' => true,
            'empty_data' => null,
            'class' => 'PROCERGS\LoginCidadao\NotificationBundle\Entity\Category',
            'property' => 'name',
            'query_builder' => function (CategoryRepository $repository) use ($person, $clientId) {
                return $repository->getOwnedCategoriesQuery($person)
                        ->andWhere('c.id = :clientId')
                        ->setParameter('clientId', $clientId);
            }
        ));
        $builder->add('client_id', 'hidden',
            array('required' => false, 'mapped' => false, 'data' => $clientId));
        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use (&$receiversConfig, &$person, $clientId) {
            $entity                           = $event->getData();
            $form                             = $event->getForm();
            $receiversConfig['query_builder'] = function (PersonRepository $repository) use ($clientId, &$entity) {
                $sql = $repository->getFindAuthorizedByClientIdQuery($clientId);
                if (!empty($entity['receivers'])) {
                    $sql->andWhere('p.id in (:receivers)');
                    $sql->setParameter('receivers', $entity['receivers']);
                }
                return $sql;
            };
            $form->add('receivers', 'ajax_choice', $receiversConfig);
        });
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use (&$receiversConfig, &$person, &$clientId) {
            $entity                           = $event->getData();
            $form                             = $event->getForm();
            $receiversConfig['query_builder'] = function (PersonRepository $repository) {
                $sql = $repository->createQueryBuilder('u');
                $sql->where('1 != 1');
                return $sql;
            };
            $form->add('receivers', 'ajax_choice', $receiversConfig);
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\NotificationBundle\Entity\Broadcast'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'broadcast_settings';
    }
}
