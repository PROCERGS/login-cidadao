<?php

namespace PROCERGS\LoginCidadao\IgpBundle\Event;

use PROCERGS\LoginCidadao\BadgesControlBundle\Model\AbstractBadgesEventSubscriber;
use PROCERGS\LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent;
use PROCERGS\LoginCidadao\BadgesControlBundle\Event\ListBearersEvent;
use PROCERGS\LoginCidadao\IgpBundle\Entity\Badge;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\BadgesControlBundle\Model\BadgeInterface;

class BadgesSubscriber extends AbstractBadgesEventSubscriber
{

    /** @var TranslatorInterface */
    protected $translator;

    /** @var EntityManager */
    protected $em;

    public function __construct(TranslatorInterface $translator,
                                EntityManager $em)
    {
        $this->translator = $translator;
        $this->em = $em;
        $this->registerBadge('valid_id_card_rs',
                             $translator->trans('valid_id_card_rs.description',
                                                array(), 'badges'),
                                                array('counter' => 'count'));
        $this->setName('igp');
    }

    public function onBadgeEvaluate(EvaluateBadgesEvent $event)
    {
        $this->checkRg($event);
    }
    
    protected function checkRg(EvaluateBadgesEvent $event)
    {
        $person = $event->getPerson();
        $count = $this->em->getRepository('PROCERGSLoginCidadaoIgpBundle:IgpIdCard')->getCountByPerson($person);
        if ($count) {
            $event->registerBadge($this->getBadge('valid_id_card_rs', true));
        }
    }

    public function onListBearers(ListBearersEvent $event)
    {
        $filterBadge = $event->getBadge();
        if ($filterBadge instanceof BadgeInterface) {
            $countMethod = $this->badges[$filterBadge->getName()]['counter'];
            $count = $this->{$countMethod}($filterBadge->getData());

            $event->setCount($filterBadge, $count);
        } else {
            foreach ($this->badges as $name => $badge) {
                $countMethod = $badge['counter'];
                $count = $this->{$countMethod}();
                $badge = new Badge($this->getName(), $name);

                $event->setCount($badge, $count);
            }
        }
    }

    protected function getBadge($name, $data)
    {
        if (array_key_exists($name, $this->getAvailableBadges())) {
            return new Badge($this->getName(), $name, $data);
        } else {
            throw new Exception("Badge $name not found in namespace {$this->getName()}.");
        }
    }

    protected function count()
    {
        return $this->em->getRepository('PROCERGSLoginCidadaoIgpBundle:IgpIdCard')->getCount();
    }

}
