<?php

namespace LoginCidadao\BadgesControlBundle;

final class BadgesEvents
{

    /**
     * The badges.evaluate event is thrown each time it's needed
     * to evaluate the badges for a Person object.
     *
     * The event listener receives an
     * LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent instance
     *
     * @var string
     */
    const BADGES_EVALUATE = 'badges.evaluate';

    /**
     * The badges.list.available event is thrown each time it's needed
     * to list the available badges of the application.
     *
     * The event listener receives an
     * LoginCidadao\BadgesControlBundle\Event\ListBadgesEvent instance
     *
     * @var string
     */
    const BADGES_LIST_AVAILABLE = 'badges.list.available';

    /**
     * The badges.register.evaluator event is thrown each time
     * an evaluator is registered.
     *
     * The event listener receives an
     * LoginCidadao\BadgesControlBundle\Model\BadgeEvaluatorInterface instance
     *
     * @var string
     */
    const BADGES_REGISTER_EVALUATOR = 'badges.register.evaluator';

    /**
     * The badges.list.bearers event is thrown each time it's needed to list
     * the bearers of each badge or only one specific badge.
     *
     * The event listener receives an
     * LoginCidadao\BadgesControlBundle\Event\ListBearersEvent instance
     *
     * @var string
     */
    const BADGES_LIST_BEARERS = 'badges.list.bearers';

}
