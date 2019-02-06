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

use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Entity\SentEmail;
use LoginCidadao\CoreBundle\Entity\SentEmailRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\SupportBundle\Exception\PersonNotFoundException;
use LoginCidadao\SupportBundle\Model\PersonalData;
use LoginCidadao\SupportBundle\Model\SupportPerson;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SupportHandler
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var PersonRepository */
    private $personRepository;

    /** @var SentEmailRepository */
    private $sentEmailRepository;

    /**
     * SupportHandler constructor.
     * @param AuthorizationCheckerInterface $authChecker
     * @param PersonRepository $personRepository
     * @param SentEmailRepository $sentEmailRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        PersonRepository $personRepository,
        SentEmailRepository $sentEmailRepository
    ) {
        $this->authChecker = $authChecker;
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

    public function getInitialMessage($id): ?SentEmail
    {
        /** @var SentEmail $sentEmail */
        $sentEmail = $this->sentEmailRepository->find($id);

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
