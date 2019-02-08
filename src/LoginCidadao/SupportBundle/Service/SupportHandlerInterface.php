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

interface SupportHandlerInterface
{
    public function getSupportPerson($id): SupportPerson;

    public function getThirdPartyConnections(IdentifiablePersonInterface $person): array;

    public function getPhoneMetadata(IdentifiablePersonInterface $person): array;

    public function getInitialMessage($ticket): ?SentEmail;

    public function getValidationMap(SupportPerson $person): array;
}
