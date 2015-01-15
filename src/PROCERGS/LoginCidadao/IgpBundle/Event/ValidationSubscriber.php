<?php

namespace PROCERGS\LoginCidadao\IgpBundle\Event;

use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Model\IdCardInterface;
use PROCERGS\LoginCidadao\ValidationControlBundle\ValidationEvents;
use PROCERGS\LoginCidadao\ValidationControlBundle\Event\IdCardValidateEvent;
use PROCERGS\LoginCidadao\IgpBundle\Validator\IgpValidations;
use PROCERGS\LoginCidadao\IgpBundle\Entity\IgpWs;

class ValidationSubscriber implements EventSubscriberInterface
{

    const REQUIRED_ISO6 = 'BR-RS';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var IgpWs
     */
    protected $igpWs;

    public function __construct(TranslatorInterface $translator,
                                EntityManager $em, IgpWs $igpWs)
    {
        $this->translator = $translator;
        $this->em = $em;
        $this->igpWs = $igpWs;
    }

    public static function getSubscribedEvents()
    {
        return array(
            ValidationEvents::ID_CARD_FORM_PRE_SET_DATA => array('onInitializeForm'),
            ValidationEvents::ID_CARD_FORM_PRE_SUBMIT => array('onInitializeForm'),
            ValidationEvents::ID_CARD_VALIDATE => array('onValidate'),
        );
    }

    public function onInitializeForm(FormEvent $form)
    {
        $data = $form->getData();
        if (!$data) {
            return;
        }
        $iso6 = null;
        if ($data instanceof IdCardInterface) {
            if ($data->getState()) {
                $iso6 = $data->getState()->getIso6();
            }
        } else {
            $state = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:State')->find($data['state']);
            $iso6 = $state->getIso6();
        }

        if ($iso6 == self::REQUIRED_ISO6) {
            $igpForm = $form->getForm();
            $igpForm->add('igp', 'lc_igpidcardformtype',
                          array('mapped' => false));
        }
    }

    public function onValidate(IdCardValidateEvent $event)
    {
        $idCard = $event->getIdCard();
        if (!($idCard instanceof IdCardInterface)) {
            return;
        }

        $state = $idCard->getState();
        if (!($state instanceof State) || $state->getIso6() !== self::REQUIRED_ISO6) {
            return;
        }

        $validatorContext = $event->getValidatorContext();
        $constraint = $event->getConstraint();
        $rgNum = $idCard->getValue();

        if (strlen($rgNum) != 10) {
            $validatorContext->addViolationAt('value',
                                              IgpValidations::MESSAGE_LENGTH);
        }
        if (IgpValidations::checkIdCardNumber($rgNum) === false) {
            $validatorContext->addViolationAt('value',
                                              IgpValidations::MESSAGE_INVALID);
        }

        $this->igpWs->setRg($rgNum);
        $res = $this->igpWs->consultar();
        if ($res === null) {
            $validatorContext->addViolationAt('value',
                                              IgpValidations::MESSAGE_WEBSERVICE_UNAVAILABLE);
        } else {
            if ($res['cod_retorno'] != 1) {
                $validatorContext->addViolationAt('value',
                                                  $res['mensagem_retorno']);
            }
        }
    }

}
