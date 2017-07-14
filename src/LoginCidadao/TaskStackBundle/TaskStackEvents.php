<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle;

class TaskStackEvents
{
    /**
     * Event used to gather login-related tasks.
     */
    const GET_LOGIN_TASKS = 'task_stack.get_login_tasks';

    /**
     * Event used to gather general tasks.
     */
    const GET_TASKS = 'task_stack.get_tasks';
}
