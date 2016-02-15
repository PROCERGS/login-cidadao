<?php

namespace LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\APIBundle\Entity\ActionLogRepository;

class AccessLogController extends Controller
{

    /**
     * @Route("/logins/list", name="lc_access_log_list")
     * @Template()
     */
    public function listAction()
    {
        $repo = $this->getActionLogRepository();
        $logs = $repo->findLoginsByPerson($this->getUser(), 50);

        return compact('logs');
    }

    /**
     * @return ActionLogRepository
     */
    private function getActionLogRepository()
    {
        return $this->getDoctrine()->getRepository('LoginCidadaoAPIBundle:ActionLog');
    }
}
