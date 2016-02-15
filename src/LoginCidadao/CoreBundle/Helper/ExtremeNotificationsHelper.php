<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Helper;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class ExtremeNotificationsHelper
{
    /** @var Session */
    private $session;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(Session $session,
                                TranslatorInterface $translator)
    {
        $this->session    = $session;
        $this->translator = $translator;
    }

    public function add($id, $parameters = array())
    {
        $translated = $this->translator->trans($id, $parameters);
        $this->commit($translated);
    }

    public function addTransChoice($id, $number, $parameters = array())
    {
        $translated = $this->translator->transChoice($id, $number, $parameters);
        $this->commit($translated);
    }

    private function commit($translated)
    {
        $this->session->getFlashBag()->add('alert.unconfirmed.email',
            $translated);
    }
}
