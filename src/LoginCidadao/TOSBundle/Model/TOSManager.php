<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TOSBundle\Model;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\User\UserInterface;
use LoginCidadao\TOSBundle\Entity\Agreement;
use LoginCidadao\TOSBundle\Entity\AgreementRepository;
use LoginCidadao\TOSBundle\Entity\TermsOfServiceRepository;

class TOSManager
{
    /** @var EntityManager */
    private $em;

    /** @var AgreementRepository */
    private $agreementRepo;

    /** @var TermsOfServiceRepository */
    private $termsRepo;

    public function __construct(EntityManager $em,
                                AgreementRepository $agreementRepo,
                                TermsOfServiceRepository $termsRepo)
    {
        $this->em            = $em;
        $this->termsRepo     = $termsRepo;
        $this->agreementRepo = $agreementRepo;
    }

    public function hasAgreedToLatestTerms(UserInterface $user)
    {
        $latest = $this->termsRepo->findLatestTerms();

        if (!($latest instanceof TOSInterface)) {
            return true;
        }

        $agreement = $this->agreementRepo->findOneBy(array(
            'termsOfService' => $latest,
            'user' => $user
        ));

        return $agreement instanceof AgreementInterface &&
            $agreement->getAgreedAt() > $latest->getUpdatedAt();
    }

    public function setUserAgreed(UserInterface $user)
    {
        if ($this->hasAgreedToLatestTerms($user)) {
            return;
        }

        $latest = $this->termsRepo->findLatestTerms();

        $agreement = new Agreement();
        $agreement->setUser($user)
            ->setTermsOfService($latest);
        $this->em->persist($agreement);
        $this->em->flush();
    }
}
