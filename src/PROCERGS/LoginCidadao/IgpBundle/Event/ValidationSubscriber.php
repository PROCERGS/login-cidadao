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
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Exception\OutOfBoundsException;

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
    
    protected $lastIgpWsResult;
    
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
            ValidationEvents::VALIDATION_ID_CARD_PERSIST => array('onPersist'),
        );
    }

    public function onInitializeForm(FormEvent $form)
    {
        $data = $form->getData();
        if (!$data) {
            return;
        }
        $iso6 = $id = null;
        if ($data instanceof IdCardInterface) {
            if ($data->getState()) {
                $iso6 = $data->getState()->getIso6();
            }
            if ($data->getId()) {
                $id = $data->getId();
            }
        } else {
            $state = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:State')->find($data['state']);
            $iso6 = $state->getIso6();
            if ($data['id']) {
                $id = $data['id']; 
            }
        }

        if ($iso6 == self::REQUIRED_ISO6) {
            if (null === $id) {
                $igpForm = $form->getForm();
                $igpForm->add('igp', 'lc_igpidcardformtype',
                    array('mapped' => false, 'label' => 'Oficial Id Card information'));
            }
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
        try {
            $igpIdCards = $event->getValidatorContext()->getRoot()->get('igp')->getData();
        } catch (OutOfBoundsException $e) {
            $validatorContext->addViolation(IgpValidations::MESSAGE_IMUTABLE_VALID_IDCARD);
            return;
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
            } else {
                $this->lastIgpWsResult = $res;                
                if (mb_strtoupper($igpIdCards->getNomeMae()) !==  mb_strtoupper($res['nomeMae'])) {
                    $validatorContext->addViolationAt('igp.nomeMae', IgpValidations::MESSAGE_VALUE_MISMATCH);
                }
                if ($igpIdCards->getDataEmissaoCI() != (\DateTime::createFromFormat('!d/m/Y', $res['dataEmissaoCI']))) {
                    $validatorContext->addViolationAt('igp.dataEmissaoCI', IgpValidations::MESSAGE_VALUE_MISMATCH);
                }
                if (mb_strtoupper($igpIdCards->getNomeCI()) !== mb_strtoupper($res['nome'])) {
                    $validatorContext->addViolationAt('igp.nomeCI', IgpValidations::MESSAGE_VALUE_MISMATCH);
                }
                if ($res['situacao_rg'] != 1) {
                    $validatorContext->addViolationAt('igp', IgpValidations::MESSAGE_IDCARD_PROBLEM);
                }
            }
        }
    }
    
    public function onPersist(FormEvent $event)
    {
        if (null === $this->lastIgpWsResult) {
            $this->igpWs->setRg($event->getData()->getValue());
            $this->lastIgpWsResult = $this->igpWs->consultar();
            if ($this->lastIgpWsResult === null) {
                throw new \Exception($this->translator->trans(IgpValidations::MESSAGE_WEBSERVICE_UNAVAILABLE));
            }
        }
        $igpIdCargs = $event->getForm()->get('igp')->getData();
        $igpIdCargs->setIdCard($event->getForm()->getData());
        $igpIdCargs->setId($this->lastIgpWsResult['ig']);
        if (isset($this->lastIgpWsResult['nomeMae'])) {
            $igpIdCargs->setNomeMae($this->lastIgpWsResult['nomeMae']);
        }
        $igpIdCargs->setNomeCi($this->lastIgpWsResult['nome']);
        $igpIdCargs->setDataEmissaoCI(date_create_from_format('!d/m/Y', $this->lastIgpWsResult['dataEmissaoCI']));
        $igpIdCargs->setRg($this->lastIgpWsResult['rg']);
        if (isset($this->lastIgpWsResult['cpf'])) {
            $igpIdCargs->setCpf($this->lastIgpWsResult['cpf']);
        }
        $igpIdCargs->setSituacaoRg($this->lastIgpWsResult['situacao_rg']);
        $this->em->persist($igpIdCargs);        
    }

}
