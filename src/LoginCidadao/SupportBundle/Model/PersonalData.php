<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\SupportBundle\Model;

class PersonalData
{
    const HASH_ALGO = 'sha512';

    /** @var string */
    private $name;

    /** @var string */
    private $hash;

    /** @var string */
    private $challenge;

    /** @var string */
    private $value;

    /** @var bool */
    private $isValueFilled;

    /**
     * PersonalData constructor.
     * @param string $name
     * @param string $hash
     * @param string $value
     * @param bool $isValueFilled
     * @param string $challenge
     */
    public function __construct(
        string $name,
        string $value = null,
        bool $isValueFilled = null,
        string $hash = null,
        string $challenge = null
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->isValueFilled = $isValueFilled ?? (bool)$value;
        $this->challenge = self::enforceChallenge($challenge);
        $this->setHash($hash);
    }

    public static function createWithValue(string $name, ?string $value, string $challenge = null): PersonalData
    {
        if (null === $value) {
            $value = '';
        }
        return new self($name, $value, (bool)$value, null, $challenge);
    }

    public static function createWithoutValue(string $name, ?string $value, string $challenge = null): PersonalData
    {
        $challenge = self::enforceChallenge($challenge);
        $hash = self::generateHash(self::enforceChallenge($challenge), $value);

        return new self($name, null, (bool)$value, $hash, $challenge);
    }

    public function checkValue(string $value): bool
    {
        $userHash = $this->generateHash($this->getChallenge(), $value);

        return hash_equals($this->getHash(), $userHash);
    }

    private function setHash(string $hash = null)
    {
        if ($hash === null) {
            if ($this->value !== null) {
                $hash = $this->generateHash($this->getChallenge(), $this->getValue());
            } else {
                throw new \InvalidArgumentException("Hash and Value can't both be null");
            }
        }

        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getChallenge(): string
    {
        return $this->challenge;
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    public function isValueFilled(): bool
    {
        return $this->isValueFilled;
    }

    private static function generateHash(string $challenge, ?string $value): string
    {
        return hash_hmac(self::HASH_ALGO, $challenge, $value);
    }

    private static function enforceChallenge(string $challenge = null): string
    {
        return $challenge ?? bin2hex(random_bytes(10));
    }

    public function __toString(): string
    {
        if ($this->getValue() !== null) {
            return $this->getValue();
        } else {
            return $this->isValueFilled ? 'Yes' : 'No';
        }
    }
}
