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

use LoginCidadao\BadgesControlBundle\Model\AbstractBadgesEventSubscriber;
use LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent;
use LoginCidadao\BadgesControlBundle\Event\ListBearersEvent;
use LoginCidadao\BadgesControlBundle\Model\BadgeInterface;
use LoginCidadao\BadgesBundle\Model\Badge;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManager;

class BadgesSubscriber extends AbstractBadgesEventSubscriber
{
    /** @var PhoneVerificationServiceInterface */
    private $phoneVerificationService;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var EntityManager */
    protected $em;

    /** @var boolean */
    private $enabled;

    public function __construct(
        PhoneVerificationServiceInterface $phoneVerificationService,
        TranslatorInterface $translator,
        EntityManager $em,
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
        $this->checkPhoneVerified($event);
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
        } else {
            foreach ($this->badges as $name => $badge) {
                if (!array_key_exists($filterBadge->getName(), $this->badges)) {
                    continue;
                }
                $countMethod = $badge['counter'];
                $count = $this->{$countMethod}();
                $badge = new Badge($this->getName(), $name);

                $event->setCount($badge, $count);
            }
        }
    }

    protected function checkPhoneVerified(EvaluateBadgesEvent $event)
    {
        $person = $event->getPerson();
        if (!$person->getMobile()) {
            return;
        }
        $verification = $this->phoneVerificationService->getPhoneVerification($person, $person->getMobile());
        if ($verification->isVerified()) {
            $event->registerBadge($this->getBadge('phone_verified', true));
        }
    }

    protected function getBadge($name, $data)
    {
        if (array_key_exists($name, $this->getAvailableBadges())) {
            return new Badge($this->getName(), $name, $data);
        } else {
            throw new \Exception("Badge $name not found in namespace {$this->getName()}.");
        }
    }

    protected function countPhoneVerified()
    {
        return $this->em->getRepository('LoginCidadaoCoreBundle:Person')
            ->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->innerJoin('LoginCidadaoPhoneVerificationBundle:PhoneVerification', 'ph', 'WITH', 'ph.person = p')
            ->andWhere('ph.verifiedAt IS NOT NULL')
            ->getQuery()->getSingleScalarResult();
    }
}
