<?php

namespace LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\APIBundle\Security\Audit\Annotation as Audit;
use Doctrine\ORM\Query;

class AddressController extends FOSRestController
{

    /**
     * @REST\Get("/public/country", name="lc_search_country" )
     * @REST\View()
     * @Audit\Loggable(type="SELECT")
     */
    public function searchCountryAction(Request $request)
    {
        $query = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:Country')
        ->createQueryBuilder('cty')
        ->where('cty.reviewed = '.Country::REVIEWED_OK)
        ->orderBy('cty.id', 'ASC');
        return $this->handleView($this->view($query->getQuery()->getResult(Query::HYDRATE_ARRAY)));
    }

    /**
     * @REST\Get("/public/state", name="lc_search_state" )
     * @REST\View()
     * @Audit\Loggable(type="SELECT")
     */
    public function searchStateAction(Request $request)
    {
        $query = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:State')
        ->createQueryBuilder('state')
        ->where('state.reviewed = '.Country::REVIEWED_OK);
        $country = $request->get('country_id');
        if ($country) {
            $query->join('LoginCidadaoCoreBundle:Country', 'cty', 'WITH', 'state.country = cty');
            $query->andWhere('cty.id = :country');
            $query->setParameter('country', $country);
        }
        $query->orderBy('state.id', 'ASC');
        return $this->handleView($this->view($query->getQuery()->getResult(Query::HYDRATE_ARRAY)));
    }

    /**
     * @REST\Get("/public/city", name="lc_search_city" )
     * @REST\View()
     * @Audit\Loggable(type="SELECT")
     */
    public function searchCityAction(Request $request)
    {
        $query = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:City')
        ->createQueryBuilder('c')
        ->where('c.reviewed = '.Country::REVIEWED_OK);
        $country = $request->get('country_id');
        $state = $request->get('state_id');
        if ($country || $state) {
            $query->join('LoginCidadaoCoreBundle:State', 'state', 'WITH', 'c.state = state');
        }
        if ($country) {
            $query->join('LoginCidadaoCoreBundle:Country', 'cty', 'WITH', 'state.country = cty');
            $query->andWhere('cty.id = :country');
            $query->setParameter('country', $country);
        }
        if ($state) {
            $query->andWhere('state.id = :state');
            $query->setParameter('state', $state);
        }
        $query->orderBy('c.id', 'ASC');
        return $this->handleView($this->view($query->getQuery()->getResult(Query::HYDRATE_ARRAY)));
    }

}
