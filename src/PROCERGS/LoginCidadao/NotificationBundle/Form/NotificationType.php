<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\CommonFormType;
use Doctrine\ORM\Mapping\ClassMetadata;

class NotificationType extends CommonFormType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $emptyEntityQuery = function(EntityRepository $er) {
            return $er->createQueryBuilder('p')
                    ->where('p.id = :id')
                    ->setParameter('id', 0);
        };
        $em                = $this->em;
        $placesHoldersForm = $builder->create('placeholders', 'form');
        $builder->add($placesHoldersForm);
        $preSubmit         = function (FormEvent $event) use (&$em) {
            $data = $event->getData();
            $form = $event->getForm();
            $form
                ->add('person', 'entity',
                    NotificationType::getPreSubmitParams(
                        'PROCERGSLoginCidadaoCoreBundle:Person', 'id',
                        NotificationType::getPersonQuery($data)
                    )
                )
                ->add('sender', 'entity',
                    NotificationType::getPreSubmitParams(
                        'PROCERGSOAuthBundle:Client', 'randomId',
                        NotificationType::getSenderQuery($data)
                    )
                )
                ->add('category', 'entity',
                    NotificationType::getPreSubmitParams(
                        'PROCERGSLoginCidadaoNotificationBundle:Category', 'id',
                        NotificationType::getCategoryQuery($data)
                    )
                )
            ;
            if (isset($data['category']) && isset($data['sender'])) {
                $category = $em
                    ->getRepository('PROCERGSLoginCidadaoNotificationBundle:Category')
                    ->createQueryBuilder('c')
                    ->where('c.id = :id')
                    ->andWhere('c.client = :clientId')
                    ->setParameters(array('id' => $data['category'], 'clientId' => $data['sender']))
                    ->getQuery()
                    ->setFetchMode('PROCERGSLoginCidadaoNotificationBundle:Placeholder',
                        'placeholders', ClassMetadata::FETCH_EAGER)
                    ->getOneOrNullResult();

                $placesHoldersForm = $form->get('placeholders');
                foreach ($category->getPlaceholders() as $placeholder) {
                    $placesHoldersForm->add($placeholder->getName(), 'text',
                        array('required' => false, 'empty_data' => $placeholder->getDefault()));
                }
                $form->add('icon', 'text',
                    array('required' => false, 'empty_data' => $category->getDefaultIcon()));
                $form->add('title', 'text',
                    array('required' => false, 'empty_data' => $category->getDefaultTitle()));
                $form->add('shortText', 'text',
                    array('required' => false, 'empty_data' => $category->getDefaultShortText()));
            }
        };

        $builder
            ->add('icon', 'text', array('required' => false))
            ->add('title', 'text', array('required' => false))
            ->add('shortText', 'text', array('required' => false))
            ->add('text', 'text', array('required' => false))
            ->add('callbackUrl', 'text', array('required' => false))
            /*                ->add('createdAt', 'datetime',
              array('required' => false, 'widget' => 'single_text'))
              ->add('readDate', 'datetime',
              array('required' => false, 'widget' => 'single_text'))
              ->add('receivedDate', 'datetime',
              array('required' => false, 'widget' => 'single_text')) */
            ->add('person', 'entity',
                array(
                'class' => 'PROCERGSLoginCidadaoCoreBundle:Person',
                'property' => 'id',
                'query_builder' => $emptyEntityQuery
            ))
            ->add('sender', 'entity',
                array(
                'class' => 'PROCERGSOAuthBundle:Client',
                'property' => 'randomId',
                'query_builder' => $emptyEntityQuery
            ))
            ->add('expireDate', 'datetime',
                array('required' => false, 'widget' => 'single_text'))
            ->add('considerReadDate', 'datetime',
                array('required' => false, 'widget' => 'single_text'))
            ->add('category', 'entity',
                array(
                'class' => 'PROCERGSLoginCidadaoNotificationBundle:Category',
                'property' => 'id',
                'query_builder' => $emptyEntityQuery
            ))
            ->addEventListener(FormEvents::PRE_SUBMIT, $preSubmit)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\NotificationBundle\Entity\Notification',
            'csrf_protection' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_notification_type';
    }

    public static function getPreSubmitParams($class, $property, $queryBuilder)
    {
        return array(
            'class' => $class,
            'property' => $property,
            'query_builder' => $queryBuilder
        );
    }

    public static function getPersonQuery($data)
    {
        $id       = array_key_exists('person', $data) ? $data['person'] : 0;
        $clientId = array_key_exists('sender', $data) ? $data['sender'] : 0;
        $query    = function(EntityRepository $er) use ($id, $clientId) {
            return $er->createQueryBuilder('p')
                    ->innerJoin('PROCERGSLoginCidadaoCoreBundle:Authorization',
                        'a', 'WITH', 'a.person = p AND a.client = :clientId')
                    ->where('p.id = :id')
                    ->setParameters(compact('id', 'clientId'));
        };
        return $query;
    }

    public static function getSenderQuery($data)
    {
        $id       = array_key_exists('sender', $data) ? $data['sender'] : 0;
        $personId = array_key_exists('person', $data) ? $data['person'] : 0;
        $query    = function(EntityRepository $er) use ($id, $personId) {
            return $er->createQueryBuilder('c')
                    ->innerJoin('PROCERGSLoginCidadaoCoreBundle:Authorization',
                        'a', 'WITH', 'a.client = c AND a.person = :personId')
                    ->where('c.id = :id')
                    ->setParameters(compact('id', 'personId'));
        };
        return $query;
    }

    public static function getCategoryQuery($data)
    {
        $id       = array_key_exists('category', $data) ? $data['category'] : 0;
        $clientId = array_key_exists('sender', $data) ? $data['sender'] : 0;
        $query    = function(EntityRepository $er) use ($id, $clientId) {
            return $er->createQueryBuilder('c')
                    ->where('c.id = :id')
                    ->andWhere('c.client = :clientId')
                    ->setParameters(compact('id', 'clientId'));
        };
        return $query;
    }
}
