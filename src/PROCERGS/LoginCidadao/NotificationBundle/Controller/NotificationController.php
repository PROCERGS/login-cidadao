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

        $clients = array();
        foreach ($handler->getSettings($person) as $option) {
            $clients = $this->addClientOption($clients, $option);
        }

        $settings = new NotificationSettings();
        $settings->setClients($clients);

        $form = $this->createForm(new SettingsType(), $settings);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $form->getData()->persist($em);
            $em->flush();
        }

        $form = $form->createView();

        return compact('settings', 'form');
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

    private function addClientOption($clients, PersonNotificationOption $option)
    {
        $clientObj = $option->getCategory()->getClient();
        $id = $clientObj->getId();
        if (!array_key_exists($id, $clients)) {
            $clients[$id] = new ClientSettings($clientObj);
        }
        $client = $clients[$id];
        $client->getOptions()->add($option);

        return $clients;
    }

}
