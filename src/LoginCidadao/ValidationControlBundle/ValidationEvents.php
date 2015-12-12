<?php

namespace LoginCidadao\ValidationControlBundle;

final class ValidationEvents
{

    /**
     * ** EXPERIMENTAL **
     * The validation.idcard.instantiate event is triggered when an instance of
     * an ID Card is needed.
     *
     * The event listener receives an instance of
     * LoginCidadao\ValidationControlBundle\Event\InstantiateIdCardEvent
     *
     * @var string
     */
    const VALIDATION_ID_CARD_INSTANTIATE = 'validation.idcard.instantiate';

    /**
     * This event is thrown when the FormEvents::PRE_SET_DATA event from an
     * IdCard's form is triggered.
     *
     * The event listener receives a Symfony\Component\Form\FormEvent instance
     */
    const ID_CARD_FORM_PRE_SET_DATA = 'validation.idcard.form.pre_set_data';

    /**
     * This event is thrown when the FormEvents::PRE_SUBMIT event from an IdCard's
     * form is triggered.
     *
     * The event listener receives a Symfony\Component\Form\FormEvent instance
     */
    const ID_CARD_FORM_PRE_SUBMIT = 'validation.id_card.form.pre_submit';

    /**
     * This event is thrown during the validation of the IdCard class.
     *
     * The event listener receives an instance of
     * LoginCidadao\ValidationControlBundle\Event\IdCardValidateEvent
     */
    const ID_CARD_VALIDATE = 'validation.id_card.validate';
    
    const VALIDATION_ID_CARD_PERSIST = 'validation.idcard.persist';
}
