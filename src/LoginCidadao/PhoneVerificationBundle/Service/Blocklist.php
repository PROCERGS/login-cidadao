<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Mailer\TwigSwiftMailer;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use LoginCidadao\PhoneVerificationBundle\Entity\BlockedPhoneNumber;
use LoginCidadao\PhoneVerificationBundle\Entity\BlockedPhoneNumberRepository;
use LoginCidadao\PhoneVerificationBundle\Model\BlockedPhoneNumberInterface;

class Blocklist implements BlocklistInterface
{
    /** @var UserManager */
    private $userManager;

    /** @var TwigSwiftMailer */
    private $mailer;

    /** @var BlockedPhoneNumberRepository */
    private $blockedPhoneRepository;

    /** @var PhoneVerificationServiceInterface */
    private $phoneVerificationService;

    /** @var EntityManagerInterface */
    private $em;

    /** @var BlocklistOptions */
    private $options;

    /**
     * Blocklist constructor.
     * @param UserManager $userManager
     * @param TwigSwiftMailer $mailer
     * @param EntityManagerInterface $em
     * @param PhoneVerificationServiceInterface $phoneVerificationService
     * @param BlocklistOptions $options
     */
    public function __construct(
        UserManager $userManager,
        TwigSwiftMailer $mailer,
        EntityManagerInterface $em,
        PhoneVerificationServiceInterface $phoneVerificationService,
        BlocklistOptions $options
    ) {
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->em = $em;
        $this->options = $options;
        $this->blockedPhoneRepository = $this->em->getRepository(BlockedPhoneNumber::class);
        $this->phoneVerificationService = $phoneVerificationService;
    }

    /**
     * @inheritDoc
     */
    public function isPhoneBlocked(PhoneNumber $phoneNumber): bool
    {
        return $this->isManuallyBlocked($phoneNumber) || $this->isBlockedAutomatically($phoneNumber);
    }

    /**
     * @inheritDoc
     */
    public function blockByPhone(PhoneNumber $phoneNumber): array
    {
        $this->em->flush();
        $blockedUsers = $this->userManager->blockUsersByPhone($phoneNumber, UserManager::FLUSH_STRATEGY_ONCE);
        $this->notifyBlockedUsers($blockedUsers);

        return $blockedUsers;
    }

    /**
     * @inheritDoc
     */
    public function checkPhoneNumber(PhoneNumber $phoneNumber): array
    {
        $blocked = [];
        if ($this->isPhoneBlocked($phoneNumber)) {
            $blocked = $this->blockByPhone($phoneNumber);
        }

        return $blocked;
    }

    /**
     * @inheritDoc
     */
    public function addBlockedPhoneNumber(
        PhoneNumber $phoneNumber,
        PersonInterface $blockedBy
    ): BlockedPhoneNumberInterface {
        $blockedPhoneNumber = new BlockedPhoneNumber($phoneNumber, $blockedBy, new \DateTime());
        $this->em->persist($blockedPhoneNumber);
        $this->em->flush();

        return $blockedPhoneNumber;
    }

    /**
     * @inheritDoc
     */
    public function getBlockedPhoneNumberByPhone(PhoneNumber $phoneNumber): ?BlockedPhoneNumberInterface
    {
        /** @var BlockedPhoneNumberInterface $blockedPhoneNumber */
        $blockedPhoneNumber = $this->blockedPhoneRepository->findByPhone($phoneNumber);

        return $blockedPhoneNumber;
    }

    /**
     * @param PersonInterface[] $blockedUsers
     */
    private function notifyBlockedUsers(array $blockedUsers)
    {
        foreach ($blockedUsers as $person) {
            $this->mailer->sendAccountAutoBlockedMessage($person);
        }
    }

    private function isManuallyBlocked(PhoneNumber $phoneNumber): bool
    {
        return $this->blockedPhoneRepository->findByPhone($phoneNumber) instanceof BlockedPhoneNumberInterface;
    }

    private function isBlockedAutomatically(PhoneNumber $phoneNumber): bool
    {
        if ($this->options->isAutoBlockEnabled()) {
            $autoBlockLimit = $this->options->getAutoBlockPhoneLimit();

            return $this->phoneVerificationService->countVerified($phoneNumber) >= $autoBlockLimit;
        }

        return false;
    }
}
