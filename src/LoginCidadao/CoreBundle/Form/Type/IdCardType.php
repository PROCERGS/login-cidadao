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

use LoginCidadao\CoreBundle\Entity\IdCard;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Entity\Country;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use LoginCidadao\ValidationControlBundle\ValidationEvents;

class IdCardType extends AbstractType
{
    protected $countryAcronym;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct($countryAcronym, EventDispatcherInterface $dispatcher)
    {
        $this->countryAcronym = $countryAcronym;
        $this->dispatcher = $dispatcher;
    }

    /**
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countryAcronym = $this->countryAcronym;
        $builder->add('id', HiddenType::class, ['required' => false]);
        $builder->add('state', EntityType::class, [
            'required' => true,
            'class' => 'LoginCidadaoCoreBundle:State',
            'choice_label' => 'name',
            'attr' => ['readonly' => true],
            'query_builder' => function (EntityRepository $er) use ($countryAcronym) {
                return $er->createQueryBuilder('s')
                    ->join('LoginCidadaoCoreBundle:Country', 'c',
                        'WITH', 's.country = c')
                    ->where('s.reviewed = '.Country::REVIEWED_OK)
                    ->andWhere('c.iso2 = :country')
                    ->setParameter('country', $countryAcronym)
                    ->orderBy('s.name', 'ASC');
            },
        ]);
        $builder->add('issuer', TextType::class, ['required' => true]);
        $builder->add('value', TextType::class, ['required' => true, 'label' => 'Idcard value']);

        $dispatcher = $this->dispatcher;
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($dispatcher) {
                $dispatcher->dispatch(ValidationEvents::ID_CARD_FORM_PRE_SET_DATA,
                    $event);
            });
        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($dispatcher) {
                $dispatcher->dispatch(ValidationEvents::ID_CARD_FORM_PRE_SUBMIT,
                    $event);
            });
    }

    /**
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => IdCard::class,
        ]);
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'lc_idcard_form';
    }
}
