<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Model\NotificationSettings;
use PROCERGS\LoginCidadao\NotificationBundle\Form\SettingsType;
use PROCERGS\LoginCidadao\NotificationBundle\Model\ClientSettings;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\PersonNotificationOption;

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
    public function editSettingsAction(Request $request)
    {
        $person = $this->getUser();
        $handler = $this->getNotificationHandler();
        $handler->initializeSettings($person);

        $form = $this->createForm(new SettingsType(),
                                  $handler->getGroupedSettings($person));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $form->getData()->persist($em);
            $em->flush();
            return $this->redirect($this->generateUrl('lc_notifications_settings'));
        }

        return array('form' => $form->createView());
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

    /**
     * @return NotificationHandlerInterface
     */
    private function getNotificationHandler()
    {
        return $this->get('procergs.notification.handler');
    }

}
