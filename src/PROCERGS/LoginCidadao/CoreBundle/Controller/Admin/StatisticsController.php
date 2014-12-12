<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\PersonFilterFormType;
use PROCERGS\LoginCidadao\CoreBundle\Helper\GridHelper;

/**
 * @Route("/admin/statistics")
 */
class StatisticsController extends Controller
{

    /**
     * @Route("/", name="lc_admin_statistics")
     * @Template()
     */
    public function indexAction(Request $request)
    {
      return true;
    }

    /**
     * @Route("/users/badges", name="lc_admin_statistics_user_badge")
     * @Template()
     */
    public function usersByBadgesAction() {
      $badgesHandler = $this->get('badges.handler');

      $badges = $badgesHandler->getAvailableBadges();
      foreach ($badges as $client => $badge) {
        foreach ($badge as $name => $desc) {
          $filterBadge = new \PROCERGS\LoginCidadao\BadgesBundle\Model\Badge($client, $name, 2);
          $count = $badgesHandler->countBearers($filterBadge);
          $data[] = $count;
        }
      }
      return array("data" => $data);
    }

    /**
     * @Route("/users/region/{type}", name="lc_admin_statistics_user_region")
     * @Template()
     */
    public function usersByRegionAction($type) {
      $em = $this->getDoctrine()->getManager();
      $repo = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Person');
      if ($type == "country") {
        $data = $repo->getCountByCountry();
      } else {
        $data = $repo->getCountByState();
      }

      return $this->render('PROCERGSLoginCidadaoCoreBundle:Admin\Statistics:usersByRegion.html.twig', array('data' => $data ));
    }

    /**
     * @Route("/users/services", name="lc_admin_statistics_user_services")
     * @Template()
     */
    public function usersByServicesAction() {
      $em = $this->getDoctrine()->getManager();
      $repo = $em->getRepository('PROCERGSOAuthBundle:Client');
      $data = $repo->getCountPerson();

      return array("data" => $data);
    }

  }
