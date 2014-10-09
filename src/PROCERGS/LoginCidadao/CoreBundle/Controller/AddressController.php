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
     * @Route("/postalcode/search", name="lc_search_postalcode")
     * @Template()
     */
    public function searchPostalCodeAction(Request $request)
    {
        $form = $this->createForm('search_postalcode_form_type');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $busca = $this->get('procergs_logincidadao.dne');
            $ceps = $busca->find(array(
                'logradouro' => $form->get('adress')->getData(),
                'localidade' => $form->get('city')->getData(),
                'numero' => $form->get('adressnumber')->getData(),
                'state' => $form->get('state')->getData()->getAcronym()
            ));
        } else {
            $ceps = array();
        }
        return array(
            'form' => $form->createView(),
            'ceps' => $ceps
        );
    }

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
