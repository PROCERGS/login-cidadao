<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Model;

class SelectMotherInitialsChallenge extends AbstractChallenge
{
    public const CHALLENGE_NAME = 'select_mother_initials';

    /** @var string[] */
    private $choices;

    /**
     * SelectMotherInitialsChallenge constructor.
     * @param int $attemptsLeft
     * @param string $cpf
     * @param string[] $choices
     */
    public function __construct(int $attemptsLeft, string $cpf, array $choices)
    {
        $this->choices = $choices;
        parent::__construct($attemptsLeft, $cpf);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::CHALLENGE_NAME;
    }

    /**
     * @return string[]
     */
    public function getChoices(): array
    {
        return $this->choices;
    }
}
