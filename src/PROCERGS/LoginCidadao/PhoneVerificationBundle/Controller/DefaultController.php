<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\PhoneVerificationBundle\Controller;

use LoginCidadao\PhoneVerificationBundle\Event\PhoneChangedEvent;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/verify_resend", name="resend_verification_code")
     */
    public function indexAction()
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->get('event_dispatcher');

        $event = new PhoneChangedEvent($this->getUser(), $this->getUser()->getMobile());
        $dispatcher->dispatch(PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED, $event);
        die("ok");

        return $this->render(
            'PROCERGSLoginCidadaoPhoneVerificationBundle:Default:index.html.twig',
            array('name' => $name)
        );
    }
}
