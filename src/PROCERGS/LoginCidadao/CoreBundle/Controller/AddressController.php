<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Uf;

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
                'uf' => $form->get('uf')->getData()->getAcronym()
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
     * @Route("/uf/country/{id}", name="lc_search_uf_by_country", defaults={"id" = ""})
     * @Template()
     */
    public function viewUfAction($id)
    {
        $result = array();
        if (is_numeric($id)) {
            $result = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:Uf')
            ->createQueryBuilder('u')
            ->select('u.id, u.name')
            ->where('u.country = :country')
            ->setParameters(array('country' => new Country($id)))
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
        }
        return new JsonResponse($result);
    }
    
    /**
     * @Route("/city/uf/{id}", name="lc_search_city_by_uf", defaults={"id" = ""})
     * @Template()
     */
    public function viewCityAction($id)
    {
        $result = array();
        if (is_numeric($id)) {
            $result = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:City')
            ->createQueryBuilder('u')
            ->select('u.id, u.name')
            ->where('u.uf = :uf')
            ->setParameters(array('uf' => new Uf($id)))
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
        }
        return new JsonResponse($result);
    }
    

}
