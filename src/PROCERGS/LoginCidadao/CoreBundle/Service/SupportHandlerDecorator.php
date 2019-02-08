<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Service;

use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Entity\SentEmail;
use LoginCidadao\CoreBundle\Model\IdentifiablePersonInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\SupportBundle\Model\SupportPerson;
use LoginCidadao\SupportBundle\Service\SupportHandlerInterface;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;

class SupportHandlerDecorator implements SupportHandlerInterface
{
    /** @var SupportHandlerInterface */
    private $parent;

    /** @var MeuRSHelper */
    private $rsHelper;

    /** @var PersonRepository */
    private $personRepo;

    /**
     * SupportHandlerDecorator constructor.
     * @param SupportHandlerInterface $parent
     * @param MeuRSHelper $rsHelper
     * @param PersonRepository $personRepo
     */
    public function __construct(SupportHandlerInterface $parent, MeuRSHelper $rsHelper, PersonRepository $personRepo)
    {
        $this->parent = $parent;
        $this->rsHelper = $rsHelper;
        $this->personRepo = $personRepo;
    }

    public function getThirdPartyConnections(IdentifiablePersonInterface $person): array
    {
        if (!$person instanceof PersonInterface) {
            $person = $this->personRepo->find($person->getId());
        }

        $connections = $this->parent->getThirdPartyConnections($person);

        if ($person instanceof PersonInterface) {
            $personRS = $this->rsHelper->getPersonMeuRS($person, true);

            $connections['nfg'] = $personRS->getNfgAccessToken() !== null;
        }

        return $connections;
    }

    public function getSupportPerson($id): SupportPerson
    {
        return $this->parent->getSupportPerson($id);
    }

    public function getPhoneMetadata(IdentifiablePersonInterface $person): array
    {
        return $this->parent->getPhoneMetadata($person);
    }

    public function getInitialMessage($ticket): ?SentEmail
    {
        return $this->parent->getInitialMessage($ticket);
    }

    public function getValidationMap(SupportPerson $person): array
    {
        return $this->parent->getValidationMap($person);
    }
}
