<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\CategoryRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonRepository;

class BroadcastType extends AbstractType
{

    private $person;
    private $clientId;

    public function __construct(PersonInterface $person, $clientId)
    {
        $this->person = $person;
        $this->clientId = $clientId;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $person = $this->person;
        $clientId = $this->clientId;

        $builder
            ->add('receivers', null,
                  array(
                'class' => 'PROCERGSLoginCidadaoCoreBundle:Person',
                'property' => 'fullName',
                'query_builder' => function (PersonRepository $repository) use ($clientId) {
                    return $repository->getFindAuthorizedByClientIdQuery($clientId);
                }
            ))
            ->add('category', 'entity',
                  array(
                'class' => 'PROCERGS\LoginCidadao\NotificationBundle\Entity\Category',
                'property' => 'name',
                'query_builder' => function (CategoryRepository $repository) use ($person, $clientId) {
                    return $repository->getOwnedCategoriesQuery($person)
                        ->andWhere('c.id = :clientId')
                        ->setParameter('clientId', $clientId);
                }
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
        return 'notification_broadcast';
    }

}
