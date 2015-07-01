<?php
/*
 *  This file is part of the login-cidadao project or it's bundles.
 *
 *  (c) Guilherme Donato <guilhermednt on github>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace LoginCidadao\TOSBundle\Form\DataTransformer;

use DateTime;
use Symfony\Component\Form\DataTransformerInterface;

class DateToBooleanTransformer implements DataTransformerInterface
{

    /**
     * Transforms a boolean to a date.
     *
     * @param DateTime $date
     *
     * @return boolean
     */
    public function transform($date)
    {
        if (!$date) {
            return false;
        }
        return true;
    }

    /**
     * Transforms a boolean to date.
     *
     * @param boolean $checked
     *
     * @return DateTime
     */
    public function reverseTransform($checked)
    {
        if ($checked) {
            return date('Y-m-d H:i:s');
        } else {
            return null;
        }
    }
}
