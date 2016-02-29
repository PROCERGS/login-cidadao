<?php

namespace PROCERGS\LoginCidadao\CoreBundle;

class DynamicFormEvents
{
    /**
     * This event is triggered just after the form information gets persisted.
     */
    const POST_FORM_VALIDATION = 'dynamicform_post_validation';

    /**
     * This event is triggered just after the form information gets persisted.
     */
    const POST_FORM_EDIT       = 'dynamicform_post_edit';

    /**
     * This event is triggered before redirecting the user to the target URL.
     */
    const PRE_REDIRECT = 'dynamicform_pre_redirect';

}