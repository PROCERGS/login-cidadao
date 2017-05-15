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

use Doctrine\ORM\EntityManager;
use libphonenumber\PhoneNumber;
use LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository;
use LoginCidadao\PhoneVerificationBundle\Event\PhoneVerificationEvent;
use LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent;
use LoginCidadao\PhoneVerificationBundle\Exception\VerificationNotSentException;
use LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerification;
use LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerificationRepository;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PhoneVerificationService implements PhoneVerificationServiceInterface
{
    /** @var PhoneVerificationOptions */
    private $options;

    /** @var EntityManager */
    private $em;

    /** @var PhoneVerificationRepository */
    private $phoneVerificationRepository;

    /** @var SentVerificationRepository */
    private $sentVerificationRepository;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * PhoneVerificationService constructor.
     * @param PhoneVerificationOptions $options
     * @param EntityManager $em
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        PhoneVerificationOptions $options,
        EntityManager $em,
        EventDispatcherInterface $dispatcher
    ) {
        $this->options = $options;
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->phoneVerificationRepository = $this->em
            ->getRepository('LoginCidadaoPhoneVerificationBundle:PhoneVerification');
        $this->sentVerificationRepository = $this->em
            ->getRepository('LoginCidadaoPhoneVerificationBundle:SentVerification');
    }

    /**
     * Gets phone verification record (PhoneVerificationInterface) for the given phone number.
     *
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public function getPhoneVerification(PersonInterface $person, PhoneNumber $phone)
    {
        /** @var PhoneVerificationInterface $phoneVerification */
        $phoneVerification = $this->phoneVerificationRepository->findOneBy(
            [
                'person' => $person,
                'phone' => $phone,
            ]
        );

        return $phoneVerification;
    }

    /**
     * @param PersonInterface $person
     * @param mixed $id
     * @return PhoneVerificationInterface
     */
    public function getPhoneVerificationById($id, PersonInterface $person = null)
    {
        $criteria = ['id' => $id];
        if ($person) {
            $criteria['person'] = $person;
        }

        /** @var PhoneVerificationInterface $phoneVerification */
        $phoneVerification = $this->phoneVerificationRepository->findOneBy($criteria);

        return $phoneVerification;
    }

    /**
     * @param PersonInterface $person
     * @param mixed $id
     * @return PhoneVerificationInterface
     */
    public function getPendingPhoneVerificationById($id, PersonInterface $person = null)
    {
        $criteria = [
            'id' => $id,
            'verifiedAt' => null,
        ];
        if ($person) {
            $criteria['person'] = $person;
        }

        /** @var PhoneVerificationInterface $phoneVerification */
        $phoneVerification = $this->phoneVerificationRepository->findOneBy($criteria);

        return $phoneVerification;
    }

    /**
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public function createPhoneVerification(PersonInterface $person, PhoneNumber $phone)
    {
        $existingVerification = $this->getPhoneVerification($person, $phone);
        if ($existingVerification instanceof PhoneVerificationInterface) {
            $this->removePhoneVerification($existingVerification);
        }

        $phoneVerification = new PhoneVerification();
        $phoneVerification->setPerson($person)
            ->setPhone($phone)
            ->setVerificationCode($this->generateVerificationCode())
            ->setVerificationToken($this->generateVerificationToken());

        $this->em->persist($phoneVerification);
        $this->em->flush($phoneVerification);

        return $phoneVerification;
    }

    /**
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface|null
     */
    public function getPendingPhoneVerification(PersonInterface $person, PhoneNumber $phone)
    {
        /** @var PhoneVerificationInterface $phoneVerification */
        $phoneVerification = $this->phoneVerificationRepository->findOneBy(
            [
                'person' => $person,
                'phone' => $phone,
                'verifiedAt' => null,
            ]
        );

        return $phoneVerification;
    }

    /**
     * @param PersonInterface $person
     * @return PhoneVerificationInterface[]
     */
    public function getAllPendingPhoneVerification(PersonInterface $person)
    {
        /** @var PhoneVerificationInterface[] $phoneVerification */
        $phoneVerification = $this->phoneVerificationRepository->findBy(
            [
                'person' => $person,
                'verifiedAt' => null,
            ]
        );

        return $phoneVerification;
    }

    /**
     * @param PhoneVerificationInterface $phoneVerification
     * @return bool
     */
    public function removePhoneVerification(PhoneVerificationInterface $phoneVerification)
    {
        $this->em->remove($phoneVerification);
        $this->em->flush($phoneVerification);

        return true;
    }

    /**
     * @param PersonInterface $person
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public function enforcePhoneVerification(PersonInterface $person, PhoneNumber $phone)
    {
        $phoneVerification = $this->getPhoneVerification($person, $phone);

        return $phoneVerification ?: $this->createPhoneVerification($person, $phone);
    }

    private function generateVerificationCode()
    {
        $length = $this->options->getLength();
        $useNumbers = $this->options->isUseNumbers();
        $useLower = $this->options->isUseLowerCase();
        $useUpper = $this->options->isUseUpperCase();

        $keySpace = $useNumbers ? '0123456789' : '';
        $keySpace .= $useLower ? 'abcdefghijklmnopqrstuvwxyz' : '';
        $keySpace .= $useUpper ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : '';

        $code = '';
        $max = mb_strlen($keySpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $code .= $keySpace[random_int(0, $max)];
        }

        return $code;
    }

    private function generateVerificationToken()
    {
        $length = $this->options->getVerificationTokenLength();

        // We divide by 2 otherwise the resulting string would be twice as long
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Verifies code without dispatching any event or making any changes.
     *
     * @param $provided
     * @param $expected
     * @return bool
     */
    public function checkVerificationCode($provided, $expected)
    {
        if ($this->options->isCaseSensitive()) {
            return $provided === $expected;
        } else {
            return strtolower($provided) === strtolower($expected);
        }
    }

    /**
     * Verifies a phone and dispatches event.
     *
     * @param PhoneVerificationInterface $phoneVerification
     * @param $providedCode
     * @return bool
     */
    public function verify(PhoneVerificationInterface $phoneVerification, $providedCode)
    {
        if ($this->checkVerificationCode($providedCode, $phoneVerification->getVerificationCode())) {
            $phoneVerification->setVerifiedAt(new \DateTime());
            $this->em->persist($phoneVerification);
            $this->em->flush($phoneVerification);

            $event = new PhoneVerificationEvent($phoneVerification, $providedCode);
            $this->dispatcher->dispatch(PhoneVerificationEvents::PHONE_VERIFIED, $event);

            return true;
        } else {
            return false;
        }
    }

    public function sendVerificationCode(PhoneVerificationInterface $phoneVerification, $forceSend = false)
    {
        $nextDate = $this->getNextResendDate($phoneVerification);
        if (!$forceSend && $nextDate > new \DateTime()) {
            // We can't resend the verification code yet
            $retryAfter = $nextDate->getTimestamp() - time();
            throw new TooManyRequestsHttpException(
                $retryAfter,
                "tasks.verify_phone.resend.errors.too_many_requests"
            );
        }

        $event = new SendPhoneVerificationEvent($phoneVerification);
        $this->dispatcher->dispatch(PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED, $event);

        $sentVerification = $event->getSentVerification();

        if (!$sentVerification) {
            throw new VerificationNotSentException();
        }

        return $sentVerification;
    }

    public function registerVerificationSent(SentVerificationInterface $sentVerification)
    {
        $this->em->persist($sentVerification);
        $this->em->flush($sentVerification);

        return $sentVerification;
    }

    public function getLastSentVerification(PhoneVerificationInterface $phoneVerification)
    {
        return $this->sentVerificationRepository->getLastVerificationSent($phoneVerification);
    }

    public function getNextResendDate(PhoneVerificationInterface $phoneVerification)
    {
        $lastSentVerification = $this->getLastSentVerification($phoneVerification);
        if (!$lastSentVerification) {
            return new \DateTime();
        }

        $timeout = \DateInterval::createFromDateString($this->options->getSmsResendTimeout());

        return $lastSentVerification->getSentAt()->add($timeout);
    }

    public function verifyToken(PhoneVerificationInterface $phoneVerification, $token)
    {
        if ($phoneVerification->isVerified()) {
            return true;
        }

        if ($phoneVerification->getVerificationToken() !== $token) {
            return false;
        }

        return $this->verify($phoneVerification, $phoneVerification->getVerificationCode());
    }
}
