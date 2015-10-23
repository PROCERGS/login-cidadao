<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\PersonFilterFormType;
use PROCERGS\LoginCidadao\CoreBundle\Helper\GridHelper;
use PROCERGS\LoginCidadao\BadgesControlBundle\Model\Badge;
use Symfony\Component\HttpFoundation\JsonResponse;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;

/**
 * @Route("/statistics")
 */
class StatisticsController extends Controller
{

    /**
     * @Route("/", name="lc_statistics")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return true;
    }

    /**
     * @Route("/users/badges", name="lc_statistics_user_badge")
     * @Template()
     */
    public function usersByBadgesAction()
    {
        $badgesHandler = $this->get('badges.handler');

        $badges = $badgesHandler->getAvailableBadges();
        foreach ($badges as $client => $badge) {
            foreach ($badge as $name => $desc) {
                $filterBadge     = new Badge($client, $name);
                $count           = $badgesHandler->countBearers($filterBadge);
                $b               = array_shift($count);
                $data[$client][] = $b;
            }
        }

        $em         = $this->getDoctrine()->getManager();
        $repo       = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Person');
        $totalUsers = $repo->getCountAll();

        return array("data" => $data, "totalUsers" => $totalUsers['qty']);
    }

    /**
     * @Route("/users/region/{type}", name="lc_statistics_user_region")
     * @Template()
     */
    public function usersByRegionAction($type)
    {
        $em   = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Person');
        if ($type == "country") {
            $data = $repo->getCountByCountry();
        } else {
            $data = $repo->getCountByState();
        }

        $em         = $this->getDoctrine()->getManager();
        $repo       = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Person');
        $totalUsers = $repo->getCountAll();


        return $this->render('PROCERGSLoginCidadaoCoreBundle:Statistics:usersByRegion.html.twig',
                array('data' => $data, 'totalUsers' => $totalUsers));
    }

    /**
     * @Route("/users/city/{stateId}", name="lc_statistics_user_city")
     * @Template()
     */
    public function usersByCityAction($stateId)
    {
        $em   = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Person');
        $data = $repo->getCountByCity($stateId);

        return $this->render('PROCERGSLoginCidadaoCoreBundle:Statistics:usersByCity.html.twig',
                array('data' => $data));
    }

    /**
     * @Route("/users/services", name="lc_statistics_user_services")
     * @Template()
     */
    public function usersByServicesAction(Request $request)
    {
        $data = $this->getNewUsersByService(30, null,
            $request->getRequestFormat());

        //die(var_dump($data[1]['data']));

        $evo = array();
        foreach ($data as $clientNewUsers) {
            $client = $clientNewUsers['client'];
            $total  = $clientNewUsers['total'];
            $id     = $client->getId();

            $newInPeriod  = $clientNewUsers['total_period'];
            $currentTotal = $total - $newInPeriod;

            $evo[$id] = array();
            foreach ($clientNewUsers['data'] as $clientData) {
                $date = $clientData[0];
                $currentTotal += $clientData[1];

                $evo[$id][] = array(
                    $date, $currentTotal
                );
            }
        }

        return compact('data', 'evo');
    }

    /**
     * @Route("/services/users-by-day.{_format}",
     *          name="lc_statistics_service_users_day",
     *          defaults={"_format": "html", "clientId": null}
     * )
     * @Template()
     */
    public function usersByServiceByDayAction(Request $request, $clientId = null)
    {
        $data = $this->getNewUsersByService(30, $clientId,
            $request->getRequestFormat());

        if ($request->getRequestFormat() === 'json') {
            return new JsonResponse($data);
        }

        return compact('data');
    }

    /**
     * @Route("/services/users-by-day-week.{_format}",
     *          name="lc_statistics_service_users_day_week",
     *          defaults={"_format": "html", "clientId": null}
     * )
     * @Template()
     */
    public function usersByServiceByDayOfWeekAction(Request $request)
    {
        $em      = $this->getDoctrine()->getManager();
        $repo    = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Authorization');
        $rawData = $repo->statsUsersByServiceByDayOfWeek();

        if ($request->getRequestFormat() === 'json') {
            return new JsonResponse($rawData);
        }

        $data = $rawData;

        return compact('data');
    }

    private function getNewUsersByService($days, $clientId = null,
                                          $format = 'html')
    {
        $em        = $this->getDoctrine()->getManager();
        $repo      = $em->getRepository('PROCERGSOAuthBundle:Client');
        $rawData   = $repo->statsUsersByServiceByDay($days, $clientId,
            $this->getUser());
        $rawTotals = $repo->getCountPerson($this->getUser(), $clientId);

        if ($format === 'json') {
            //return $rawData;
        }

        $totals = array();
        foreach ($rawTotals as $entry) {
            $client = $entry['client'];

            $totals[$client->getId()] = array('client' => $client, 'total' => $entry['qty']);
        }

        $data  = array();
        $total = array();
        foreach ($rawData as $stat) {
            $id     = $stat['client'];
            $client = $totals[$id]['client'];
            $total  = $totals[$id]['total'];
            $count  = $stat['users'];
            $day    = $stat['day'];
            @$totalPeriod[$id] += $count;

            $data[$id]['client']       = $client;
            $data[$id]['total_period'] = $totalPeriod[$id];
            $data[$id]['total']        = $total;
            $data[$id]['days']         = $days;
            $data[$id]['data'][]       = array($day, $count);
        }

        return $data;
    }
}
