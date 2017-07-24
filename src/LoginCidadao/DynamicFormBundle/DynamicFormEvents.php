<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle;

class DynamicFormEvents
{
    /**
     * This event is triggered just after the form information gets persisted.
     */
    const POST_FORM_VALIDATION = 'dynamicform_post_validation';

    /**
     * This event is triggered just after the form information gets persisted.
     */
    const POST_FORM_EDIT = 'dynamicform_post_edit';

    /**
     * This event is triggered before redirecting the user to the target URL.
     */
    const PRE_REDIRECT = 'dynamicform_pre_redirect';

}
