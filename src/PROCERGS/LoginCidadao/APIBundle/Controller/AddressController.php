<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpFoundation\Request;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Uf;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use Doctrine\ORM\Query;

class AddressController extends FOSRestController
{

    /**
     * @REST\Get("/public/postalcode/{postalCode}", name="lc_consultaCep2", defaults={"postalCode" = ""})
     * @REST\View()
     */
    public function viewPostalCodeAction($postalCode)
    {
        $request = $this->getRequest();
        $busca = $this->get('procergs_logincidadao.dne');
        $postalCodes = $busca->findByCep($postalCode);
        if ($postalCodes) {
            $result = array(
                'code' => 200,
                'msg' => null,
                'items' => array(
                    $postalCodes
                )
            );
        } else {
            throw new NotFoundHttpException();
        }

        $view = $this->view($result);
        return $this->handleView($view);
    }
    
    /**
     * @REST\Get("/public/country", name="lc_search_country" )
     * @REST\View()
     */
    public function searchCountryAction()
    {
        $request = $this->getRequest();
        $query = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoCoreBundle:Country')
        ->createQueryBuilder('cty')
        ->where('cty.reviewed = ' . Country::REVIEWED_OK)
        ->orderBy('cty.id', 'ASC');
        return $this->handleView($this->view($query->getQuery()->getResult(Query::HYDRATE_ARRAY)));
    }
    
    /**
     * @REST\Get("/public/uf", name="lc_search_uf" )
     * @REST\View()
     */
    public function searchUfAction(Request $request = null)
    {
        $request = $this->getRequest();
        $query = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoCoreBundle:Uf')
        ->createQueryBuilder('uf')
        ->where('uf.reviewed = ' . Country::REVIEWED_OK);
        $country = $request->get('country_id');
        if ($country) {
            $query->join('PROCERGSLoginCidadaoCoreBundle:Country', 'cty', 'WITH', 'uf.country = cty');
            $query->andWhere('cty.id = :country');
            $query->setParameter('country', $country);
        }
        $query->orderBy('uf.id', 'ASC');
        return $this->handleView($this->view($query->getQuery()->getResult(Query::HYDRATE_ARRAY)));
    }
    
    /**
     * @REST\Get("/public/city", name="lc_search_city" )
     * @REST\View()
     */
    public function searchCityAction(Request $request = null)
    {
        $request = $this->getRequest();
        $query = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoCoreBundle:City')
        ->createQueryBuilder('c')
        ->where('c.reviewed = ' . Country::REVIEWED_OK);
        $country = $request->get('country_id');
        $uf = $request->get('uf_id');
        if ($country || $uf) {
            $query->join('PROCERGSLoginCidadaoCoreBundle:Uf', 'uf', 'WITH', 'c.uf = uf');
        }
        if ($country) {
            $query->join('PROCERGSLoginCidadaoCoreBundle:Country', 'cty', 'WITH', 'uf.country = cty');
            $query->andWhere('cty.id = :country');
            $query->setParameter('country', $country);
        }
        if ($uf) {
            $query->andWhere('uf.id = :uf');
            $query->setParameter('uf', $uf);
        }
        $query->orderBy('c.id', 'ASC');
        return $this->handleView($this->view($query->getQuery()->getResult(Query::HYDRATE_ARRAY)));
    }

}
