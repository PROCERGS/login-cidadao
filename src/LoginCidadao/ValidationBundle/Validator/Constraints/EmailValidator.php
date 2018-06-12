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

use Egulias\EmailValidator\Validation\RFCValidation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\RuntimeException;

/**
 * Class EmailValidator
 * @package LoginCidadao\ValidationBundle\Validator\Constraints
 *
 * TODO: remove after update to Symfony 4.1
 * @codeCoverageIgnore
 */
class EmailValidator extends \Symfony\Component\Validator\Constraints\EmailValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($constraint->strict) {
            if (!class_exists('\Egulias\EmailValidator\EmailValidator') || !class_exists('\Egulias\EmailValidator\Validation\RFCValidation')) {
                throw new RuntimeException('Strict email validation requires egulias/email-validator');
            }

            $strictValidator = new \Egulias\EmailValidator\EmailValidator();

            if (!$strictValidator->isValid($value, new RFCValidation())) {
                if ($this->context instanceof ExecutionContextInterface) {
                    $this->context->buildViolation($constraint->message)
                        ->setParameter('{{ value }}', $this->formatValue($value))
                        ->setCode(Email::INVALID_FORMAT_ERROR)
                        ->addViolation();
                } else {
                    $this->buildViolation($constraint->message)
                        ->setParameter('{{ value }}', $this->formatValue($value))
                        ->setCode(Email::INVALID_FORMAT_ERROR)
                        ->addViolation();
                }

                return;
            }
        } else {
            parent::validate($value, $constraint);
        }
    }
}
