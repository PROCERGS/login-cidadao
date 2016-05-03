<?php

namespace LoginCidadao\InfiniteScrollBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use LoginCidadao\InfiniteScrollBundle\Model\NotificationIterable;
use LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class FragmentController extends Controller
{

    public function fragmentAction($name)
    {
        return $this->render('LoginCidadaoInfiniteScrollBundle:Default:index.html.twig',
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
                echo $notification->getId().PHP_EOL;
            }
            echo '-----'.PHP_EOL;
        }
        die();
    }

    /**
     * @return NotificationHandlerInterface
     */
    private function getNotificationHandler()
    {
        return $this->get('lc.notification.handler');
    }

}
