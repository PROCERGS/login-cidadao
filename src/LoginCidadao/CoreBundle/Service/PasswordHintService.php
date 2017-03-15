<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Service;

use Rollerworks\Bundle\PasswordStrengthBundle\Validator\Constraints\PasswordRequirements;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PasswordHintService
{
    const TRANS_TYPE_RANGE = 'range';
    const TRANS_TYPE_MIN = 'min';
    const TRANS_TYPE_MAX = 'max';
    const TRANS_TYPE_NO_LIMIT = 'no_limit';
    const TRANS_WITH_REQS = 'with_reqs';
    const TRANS_NO_REQS = 'no_reqs';

    /** @var ValidatorInterface */
    private $validator;

    /** @var TranslatorInterface */
    private $translator;

    /** @var string */
    private $userClass;

    /**
     * PasswordHintService constructor.
     * @param ValidatorInterface $validator
     * @param TranslatorInterface $translator
     * @param string $userClass
     */
    public function __construct(ValidatorInterface $validator, TranslatorInterface $translator, $userClass)
    {
        $this->validator = $validator;
        $this->translator = $translator;
        $this->userClass = $userClass;
    }

    public function getPasswordRequirements()
    {
        $requirements = [
            'min' => 0,
            'max' => null,
            'requireLetters' => false,
            'requireNumbers' => false,
            'requireSpecialCharacter' => false,
        ];

        /** @var ClassMetadata $metadata */
        $metadata = $this->validator->getMetadataFor($this->userClass);

        if (!($metadata instanceof ClassMetadata) || false === $metadata->hasPropertyMetadata('plainPassword')) {
            return $requirements;
        }
        $propertyMetadata = $metadata->getPropertyMetadata('plainPassword');
        $constraints = [];
        foreach ($propertyMetadata as $propertymetadata) {
            $constraints = array_merge($constraints, $propertymetadata->getConstraints());
        }

        foreach ($constraints as $constraint) {
            $requirements = $this->checkMin($constraint, $requirements);
            $requirements = $this->checkMax($constraint, $requirements);
            $requirements = $this->checkCharacters($constraint, $requirements);
        }

        return $requirements;
    }

    public function getHintString(array $requirements = null)
    {
        if ($requirements === null) {
            $requirements = $this->getPasswordRequirements();
        }

        $reqs = [];
        if ($requirements['requireLetters']) {
            $reqs[] = $this->translator->trans('password_hint.requirements.letters');
        }
        if ($requirements['requireNumbers']) {
            $reqs[] = $this->translator->trans('password_hint.requirements.numbers');
        }
        if ($requirements['requireSpecialCharacter']) {
            $reqs[] = $this->translator->trans('password_hint.requirements.special');
        }

        if ($requirements['min'] > 0 && $requirements['max'] !== null) {
            $type = self::TRANS_TYPE_RANGE;
        } elseif ($requirements['min'] > 0 && $requirements['max'] === null) {
            $type = self::TRANS_TYPE_MIN;
        } elseif ($requirements['min'] <= 0 && $requirements['max'] !== null) {
            $type = self::TRANS_TYPE_MAX;
        } else {
            $type = self::TRANS_TYPE_NO_LIMIT;
        }

        if (count($reqs) > 1) {
            $reqString = sprintf(
                '%s %s %s',
                implode(', ', array_slice($reqs, 0, count($reqs) - 1)),
                $this->translator->trans('password_hint.and'),
                end($reqs)
            );
        } else {
            $reqString = reset($reqs);
        }

        $hasReqs = count($reqs) > 0 ? self::TRANS_WITH_REQS : self::TRANS_NO_REQS;

        $params = [
            '%reqs%' => $reqString,
            '%min%' => $requirements['min'],
            '%max%' => $requirements['max'],
        ];

        if ($type === self::TRANS_TYPE_NO_LIMIT && count($reqs) <= 0) {
            return false;
        }

        return $this->translator->trans("password_hint.{$type}.{$hasReqs}", $params);
    }

    private function checkMin(Constraint $constraint, array $requirements)
    {
        if (property_exists($constraint, 'min')) {
            $newMin = $constraint->min;
        } elseif (property_exists($constraint, 'minLength')) {
            $newMin = $constraint->minLength;
        } else {
            return $requirements;
        }
        if ($newMin > $requirements['min']) {
            $requirements['min'] = $newMin;
        }

        return $requirements;
    }

    private function checkMax(Constraint $constraint, array $requirements)
    {
        if (property_exists($constraint, 'max')) {
            $newMax = $constraint->max;
        } else {
            return $requirements;
        }
        if ($requirements['max'] === null || $newMax < $requirements['max']) {
            $requirements['max'] = $newMax;
        }

        return $requirements;
    }

    private function checkCharacters(Constraint $constraint, array $requirements)
    {
        if (!($constraint instanceof PasswordRequirements)) {
            return $requirements;
        }

        $requirements['requireLetters'] = $constraint->requireLetters;
        $requirements['requireNumbers'] = $constraint->requireNumbers;
        $requirements['requireSpecialCharacter'] = $constraint->requireSpecialCharacter;

        return $requirements;
    }
}
