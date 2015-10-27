<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;

class AddressController extends Controller
{

    /**
     * @Route("/state/country/{id}", name="lc_search_state_by_country", defaults={"id" = ""})
     * @Template()
     */
    public function viewStateAction($id)
    {
        $result = array();
        if (is_numeric($id)) {
            $result = $this->getDoctrine()
            ->getManager ()
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:State')
            ->createQueryBuilder('u')
            ->select('u.id, u.name')
            ->where('u.country = :country')
            ->andWhere('u.reviewed = ' . State::REVIEWED_OK)
            ->setParameters(array('country' => new Country($id)))
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
        }
        return new JsonResponse($result);
    }

    /**
     * @Route("/city/state/{id}", name="lc_search_city_by_state", defaults={"id" = ""})
     * @Template()
     */
    public function viewCityAction($id)
    {
        $result = array();
        if (is_numeric($id)) {
            $result = $this->getDoctrine()
            ->getManager ()
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:City')
            ->createQueryBuilder('u')
            ->select('u.id, u.name')
            ->where('u.state = :state')
            ->andWhere('u.reviewed = ' . City::REVIEWED_OK)
            ->setParameters(array('state' => new State($id)))
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
        }
        return new JsonResponse($result);
    }


}
