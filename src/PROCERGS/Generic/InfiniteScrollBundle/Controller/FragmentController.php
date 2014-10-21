<?php

namespace PROCERGS\Generic\InfiniteScrollBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use PROCERGS\Generic\InfiniteScrollBundle\Model\NotificationIterable;
use PROCERGS\LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class FragmentController extends Controller
{

    public function fragmentAction($name)
    {
        return $this->render('PROCERGSGenericInfiniteScrollBundle:Default:index.html.twig',
                             array('name' => $name));
    }

    /**
     * @Route("/fragment/test/{offset}", name="lc_fragment_test")
     * @Template()
     */
    public function testAction($offset = 0)
    {
        $handler = $this->getNotificationHandler()
            ->getAuthenticatedHandler($this->getUser());
        $iterator = new NotificationIterable($handler, 10, $offset);

        foreach ($iterator as $notifications) {
            foreach ($notifications as $notification) {
                echo $notification->getId() . PHP_EOL;
            }
            echo '-----' . PHP_EOL;
        }
        die();
    }

    /**
     * @return NotificationHandlerInterface
     */
    private function getNotificationHandler()
    {
        return $this->get('procergs.notification.handler');
    }

}
