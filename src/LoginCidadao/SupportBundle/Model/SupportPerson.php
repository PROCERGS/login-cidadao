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

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SupportPerson
{
    /** @var mixed */
    private $id;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var PersonalData */
    private $birthday;

    /** @var PersonalData */
    private $cpf;

    /** @var PersonalData */
    private $email;

    /** @var \DateTimeInterface */
    private $emailVerifiedAt;

    /** @var PersonalData */
    private $phoneNumber;

    /** @var \DateTimeInterface */
    private $lastPasswordResetRequest;

    /** @var bool */
    private $has2FA;

    /** @var array */
    private $thirdPartyConnections = [];

    /** @var bool */
    private $isEnabled;

    /** @var \DateTimeInterface */
    private $lastUpdate;

    /** @var \DateTimeInterface */
    private $createdAt;

    /**
     * SupportPerson constructor.
     * @param PersonInterface|Person $person
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(PersonInterface $person, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->id = $person->getId();
        $this->firstName = $person->getFirstName();
        $this->lastName = $person->getSurname();
        $this->emailVerifiedAt = $person->getEmailConfirmedAt();
        $this->lastPasswordResetRequest = $person->getPasswordRequestedAt();
        $this->has2FA = $person->getGoogleAuthenticatorSecret() !== null;
        $this->isEnabled = $person->isEnabled();
        $this->lastUpdate = $person->getUpdatedAt();
        $this->createdAt = $person->getCreatedAt();

        $this->thirdPartyConnections = [
            'facebook' => $person->getFacebookId() !== null,
            'google' => $person->getGoogleId() !== null,
            'twitter' => $person->getTwitterId() !== null,
        ];

        if ($authorizationChecker->isGranted('ROLE_VIEW_USERS_CPF')) {
            $this->cpf = PersonalData::createWithValue('cpf', $person->getCpf());
        } else {
            $this->cpf = PersonalData::createWithoutValue('cpf', $person->getCpf());
        }
        if ($authorizationChecker->isGranted('ROLE_SUPPORT_VIEW_EMAIL')) {
            $this->email = PersonalData::createWithValue('email', $person->getEmailCanonical());
        } else {
            $this->email = PersonalData::createWithoutValue('email', $person->getEmailCanonical());
        }
        $this->setPhoneNumber($person, $authorizationChecker);
        $this->setBirthday($person, $authorizationChecker);
    }

    public function checkCpf(string $cpf)
    {
        return $this->cpf->checkValue($cpf);
    }

    private function setPhoneNumber(PersonInterface $person, AuthorizationCheckerInterface $authorizationChecker)
    {
        $phoneNumber = $person->getMobile();
        if ($phoneNumber instanceof PhoneNumber) {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneUtil->format($person->getMobile(), PhoneNumberFormat::E164);
        }
        if ($authorizationChecker->isGranted('ROLE_SUPPORT_VIEW_PHONE')) {
            $this->phoneNumber = PersonalData::createWithValue('phoneNumber', $phoneNumber);
        } else {
            $this->phoneNumber = PersonalData::createWithoutValue('phoneNumber', $phoneNumber);
        }
    }

    private function setBirthday(PersonInterface $person, AuthorizationCheckerInterface $authorizationChecker)
    {
        $birthday = $person->getBirthdate();
        if ($birthday instanceof \DateTimeInterface) {
            $birthday = $birthday->format('Y-m-d');
        }

        if ($authorizationChecker->isGranted('ROLE_SUPPORT_VIEW_BIRTHDAY')) {
            $this->birthday = PersonalData::createWithValue('birthday', $birthday);
        } else {
            $this->birthday = PersonalData::createWithoutValue('birthday', $birthday);
        }
    }

    public function getName(): string
    {
        return $this->getFirstName() ?? $this->getId();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return PersonalData
     */
    public function getBirthday(): PersonalData
    {
        return $this->birthday;
    }

    /**
     * @return PersonalData
     */
    public function getCpf(): PersonalData
    {
        return $this->cpf;
    }

    /**
     * @return PersonalData
     */
    public function getEmail(): PersonalData
    {
        return $this->email;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEmailVerifiedAt(): ?\DateTimeInterface
    {
        return $this->emailVerifiedAt;
    }

    /**
     * @return PersonalData
     */
    public function getPhoneNumber(): PersonalData
    {
        return $this->phoneNumber;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastPasswordResetRequest(): ?\DateTimeInterface
    {
        return $this->lastPasswordResetRequest;
    }

    /**
     * @return bool
     */
    public function isHas2FA(): bool
    {
        return $this->has2FA;
    }

    /**
     * @return array
     */
    public function getThirdPartyConnections(): array
    {
        return $this->thirdPartyConnections;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastUpdate(): \DateTimeInterface
    {
        return $this->lastUpdate;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
