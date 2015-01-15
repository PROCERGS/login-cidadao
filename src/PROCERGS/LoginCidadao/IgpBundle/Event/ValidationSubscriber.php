<?php
namespace PROCERGS\LoginCidadao\IgpBundle\Event;

use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PROCERGS\LoginCidadao\CoreBundle\PROCERGSLoginCidadaoCoreEvents;
use Symfony\Component\Form\FormEvent;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Entity\IdCard;
use PROCERGS\LoginCidadao\IgpBundle\Validator\Constraints\RG;
use PROCERGS\LoginCidadao\IgpBundle\Model\IgpIdCard;
use PROCERGS\LoginCidadao\IgpBundle\form\IgpIdCardFormType;

class ValidationSubscriber implements EventSubscriberInterface
{

    /**
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(TranslatorInterface $translator, EntityManager $em)
    {
        $this->translator = $translator;
        $this->em = $em;
    }
    
    

    public static function getSubscribedEvents()
    {
        return array(
            PROCERGSLoginCidadaoCoreEvents::ID_CARDS_FORM_PRE_SET_DATA => array(
                'onInitializeForm'
            ),
            PROCERGSLoginCidadaoCoreEvents::ID_CARDS_FORM_PRE_SUBMIT => array(
                'onInitializeForm'
            ),
        );
    }

    public function onInitializeForm(FormEvent $form)
    {
        $data = $form->getData();
        if (! $data) {
            return;
        }
        $id = null;
        if ($data instanceof IdCard) {
            if ($data->getState()) {
                $id = $data->getState()->getId();
            }
        } else {
            $id = $data['state'];
        }
        if ($id == '43') {
            $igpForm = $form->getForm();
            $igpForm->add('igp', 'lc_igpidcardformtype', array('mapped' => false));
            /*
            $igpForm->add('igp', 'text', array(
                'required' => true,
                'label' => $this->translator->trans('nomeMae'),
                'mapped' => false,
                'constraints' => array(
                    $this->rgConstraint
                )
            ));
            $igpForm->add('dataEmissaoCI', 'birthday', array(
                'required' => true,
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'mapped' => false,
                'attr' => array(
                    'pattern' => '[0-9/]*'
                ),
                'label' => $this->translator->trans('dataEmissaoCI')
            ));
            $igpForm->add('nomeCI', 'text', array(
                'required' => true,
                'mapped' => false,
                'label' => $this->translator->trans('nomeCI')
            ));
            */
        }
    }
    
}
