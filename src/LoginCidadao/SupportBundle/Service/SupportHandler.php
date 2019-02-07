<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\SupportBundle\Service;

use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Entity\SentEmail;
use LoginCidadao\CoreBundle\Entity\SentEmailRepository;
use LoginCidadao\CoreBundle\Model\IdentifiablePersonInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;
use LoginCidadao\SupportBundle\Exception\PersonNotFoundException;
use LoginCidadao\SupportBundle\Model\PersonalData;
use LoginCidadao\SupportBundle\Model\SupportPerson;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SupportHandler
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var PhoneVerificationServiceInterface */
    private $phoneVerificationService;

    /** @var PersonRepository */
    private $personRepository;

    /** @var SentEmailRepository */
    private $sentEmailRepository;

    /**
     * SupportHandler constructor.
     * @param AuthorizationCheckerInterface $authChecker
     * @param PhoneVerificationServiceInterface $phoneVerificationService
     * @param PersonRepository $personRepository
     * @param SentEmailRepository $sentEmailRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        PhoneVerificationServiceInterface $phoneVerificationService,
        PersonRepository $personRepository,
        SentEmailRepository $sentEmailRepository
    ) {
        $this->authChecker = $authChecker;
        $this->phoneVerificationService = $phoneVerificationService;
        $this->personRepository = $personRepository;
        $this->sentEmailRepository = $sentEmailRepository;
    }

    public function getSupportPerson($id): SupportPerson
    {
        $person = $this->personRepository->find($id);
        if (!$person instanceof PersonInterface) {
            throw new PersonNotFoundException();
        }

        return new SupportPerson($person, $this->authChecker);
    }

    public function getPhoneMetadata(IdentifiablePersonInterface $person): array
    {
        /** @var PersonInterface $person */
        $person = $this->personRepository->find($person->getId());
        $phone = $person->getMobile();

        if ($phone instanceof PhoneNumber) {
            $samePhoneCount = $this->personRepository->countByPhone($phone);
            $phoneVerification = $this->phoneVerificationService->getPhoneVerification($person, $phone);
        }

        return [
            'samePhoneCount' => $samePhoneCount ?? 0,
            'verification' => $phoneVerification ?? null,
        ];
    }

    public function getInitialMessage($ticket): ?SentEmail
    {
        /** @var SentEmail $sentEmail */
        $sentEmail = $this->sentEmailRepository->findOneBy(['supportTicket' => $ticket]);

        return $sentEmail;
    }

    public function getValidationMap(SupportPerson $person): array
    {
        return array_filter([
            'cpf' => $this->personalDataToValidationArray($person->getCpf(), true),
            'birthday' => $this->personalDataToValidationArray($person->getBirthday(), true),
            'email' => $this->personalDataToValidationArray($person->getEmail(), true),
            'phoneNumber' => $this->personalDataToValidationArray($person->getPhoneNumber(), true),
        ]);
    }

    private function personalDataToValidationArray(PersonalData $data, bool $skipIfValueSet = false): ?array
    {
        if (false === $data->isValueFilled()) {
            return null;
        }
        if ($skipIfValueSet && $data->getValue() !== null) {
            return null;
        }

        return [
            'name' => $data->getName(),
            'hash' => $data->getHash(),
            'challenge' => $data->getChallenge(),
        ];
    }
}
