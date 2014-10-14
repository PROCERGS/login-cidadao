<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Controller;

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
        
    }

}
