<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use LoginCidadao\CoreBundle\Entity\PersonAddress;
use LoginCidadao\CoreBundle\Model\LocationSelectData;

class PersonAddressFormType extends AbstractType
{
    /** @var array */
    protected $preferredCountries;

    /** @var array */
    protected $preferredStates;

    public function __construct(EntityManager $em)
    {
        $this->preferredCountries = $em->getRepository('LoginCidadaoCoreBundle:Country')->findPreferred();
        $this->preferredStates = $em->getRepository('LoginCidadaoCoreBundle:State')->findPreferred();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                ['label' => 'address.name']
            )
            ->add(
                'address',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                ['required' => true]
            )
            ->add(
                'addressNumber',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                ['required' => false]
            )
            ->add(
                'complement',
                'Symfony\Component\Form\Extension\Core\Type\TextType',
                ['required' => false]
            )
            ->add(
                'location',
                'LoginCidadao\CoreBundle\Form\Type\CitySelectorComboType',
                ['level' => 'city']
            );

        if (isset($this->preferredCountries[0])) {
            $id = $this->preferredCountries[0]->getId();
        } else {
            $id = null;
        }

        $builder
            ->add(
                'preferredcountries',
                'Symfony\Component\Form\Extension\Core\Type\HiddenType',
                ["data" => $id, "required" => false, "mapped" => false]
            )
            ->add('postalCode');

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $data = $event->getData();
                if ($data instanceof PersonAddress) {
                    if ($data->getCity()) {
                        $data->setState($data->getCity()->getState());
                        $data->setCountry($data->getCity()->getState()->getCountry());
                    } elseif ($data->getLocation() instanceof LocationSelectData) {
                        $data->getLocation()->toObject($data);
                    }
                }
            }
        );
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $address = $event->getData();
                if ($address instanceof PersonAddress) {
                    $address->getLocation()->toObject($address);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'LoginCidadao\CoreBundle\Entity\PersonAddress',
                'constraints' => new Constraints\Valid(),
            ]
        );
    }
}
