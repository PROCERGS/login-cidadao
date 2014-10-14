<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class NotificationController extends Controller
{

    /**
     * @Route("/notifications", name="lc_notifications")
     * @Template()
     */
    public function indexAction()
    {

    }

    /**
     * @Route("/notifications/settings", name="lc_notifications_settings")
     * @Template()
     */
    public function editSettingsAction()
    {
        $person = $this->getUser();
        $authorizations = $person->getAuthorizations();
        foreach ($authorizations as $authorization) {

        }
    }

    /**
     * @Route("/notifications/navbar/fragment/{offset}", requirements={"offset" = "\d+"}, defaults={"offset" = 0}, name="lc_notifications_navbar_fragment")
     * @Template()
     */
    public function getNavbarFragmentAction(Request $request, $offset = 0)
    {
        $notifications = $this->getDoctrine()->getManager()
            ->getRepository('PROCERGSLoginCidadaoNotificationBundle:Notification')
            ->findNextNotifications($this->getUser(), 8, $offset);

        return compact('notifications');
    }

}
