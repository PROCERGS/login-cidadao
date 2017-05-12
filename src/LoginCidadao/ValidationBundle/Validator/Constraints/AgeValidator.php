<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\ValidationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AgeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof \DateTime || !$constraint instanceof Age) {
            return;
        }

        $now = new \DateTime();
        $minAgeDate = new \DateTime("-{$constraint->min} years");
        $maxAgeDate = new \DateTime("-{$constraint->max} years");

        $error = false;
        $limit = 0;

        if ($value > $now) {
            $error = $constraint->futureError;
        } else {
            if ($constraint->min > 0 && $value > $minAgeDate) {
                $error = $constraint->minError;
                $limit = $constraint->min;
            }

            if ($value < $maxAgeDate) {
                $error = $constraint->maxError;
                $limit = $constraint->max;
            }
        }

        if ($error) {
            $this->context
                ->buildViolation($error)
                ->setParameter('{{ limit }}', $limit)
                ->addViolation();
        }
    }
}
