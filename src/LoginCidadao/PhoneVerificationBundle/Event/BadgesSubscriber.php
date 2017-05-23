<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Event;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\BadgesControlBundle\Model\AbstractBadgesEventSubscriber;
use LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent;
use LoginCidadao\BadgesControlBundle\Event\ListBearersEvent;
use LoginCidadao\BadgesControlBundle\Model\BadgeInterface;
use LoginCidadao\BadgesBundle\Model\Badge;
use LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerificationRepository;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BadgesSubscriber extends AbstractBadgesEventSubscriber
{
    /** @var PhoneVerificationServiceInterface */
    private $phoneVerificationService;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var boolean */
    private $enabled;

    public function __construct(
        PhoneVerificationServiceInterface $phoneVerificationService,
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        $enabled
    ) {
        $this->phoneVerificationService = $phoneVerificationService;
        $this->translator = $translator;
        $this->em = $em;
        $this->enabled = $enabled;

        if (!$enabled) {
            return;
        }

        $namespace = 'login-cidadao';
        $this->setName($namespace);

        $this->registerBadge(
            'phone_verified',
            $translator->trans(
                "$namespace.phone_verified.description",
                [],
                'badges'
            ),
            ['counter' => 'countPhoneVerified']
        );
    }

    public function onBadgeEvaluate(EvaluateBadgesEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        $person = $event->getPerson();
        if (!$person->getMobile()) {
            return;
        }

        $verification = $this->phoneVerificationService->getPhoneVerification($person, $person->getMobile());
        if ($verification instanceof PhoneVerificationInterface && $verification->isVerified()) {
            $badge = new Badge($this->getName(), 'phone_verified', true);
            $event->registerBadge($badge);
        }
    }

    public function onListBearers(ListBearersEvent $event)
    {
        $filterBadge = $event->getBadge();
        if ($filterBadge instanceof BadgeInterface) {
            if (!array_key_exists($filterBadge->getName(), $this->badges)) {
                return;
            }
            $countMethod = $this->badges[$filterBadge->getName()]['counter'];
            $count = $this->{$countMethod}($filterBadge->getData());

            $event->setCount($filterBadge, $count);
        }
    }

    protected function countPhoneVerified()
    {
        /** @var PhoneVerificationRepository $repo */
        $repo = $this->em->getRepository('LoginCidadaoPhoneVerificationBundle:PhoneVerification');

        return $repo->countBadges();
    }
}
