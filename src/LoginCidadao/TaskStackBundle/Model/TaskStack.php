<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Model;

class TaskStack extends \SplStack
{
    /**
     * @param TaskInterface $task
     * @return bool
     */
    public function hasTask(TaskInterface $task)
    {
        /**
         * @var TaskInterface $stacked
         */
        foreach ($this as $stacked) {
            if ($stacked->getId() === $task->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasIntentTask()
    {
        /**
         * @var TaskInterface $stacked
         */
        foreach ($this as $stacked) {
            if ($stacked instanceof IntentTask) {
                return true;
            }
        }

        return false;
    }
}
