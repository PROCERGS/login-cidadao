<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PROCERGS\LoginCidadao\APIBundle\Security\Audit\Annotation as Audit;

/**
 * @REST\Prefix("")
 */
class PersonAddressController extends BaseController
{

    /**
     * Searches cities by name and, optionally, state.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Searches cities by name and, optionally, state.",
     *   output = {
     *     "class"="PROCERGS\LoginCidadao\CoreBundle\Entity\City",
     *     "groups" = {"public"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when no city is found"
     *   }
     * )
     * @REST\View(templateVar="cities")
     * @param string  $city
     * @return \PROCERGS\LoginCidadao\CoreBundle\Entity\City
     * @throws NotFoundHttpException when no city is found
     * @REST\Get("/address/cities/search/{city}", name="api_1_get_cities")
     * @Audit\Loggable(type="SELECT")
     */
    public function getCitiesAction(Request $request, $city)
    {
        $countryId = $request->get('country_id', null);
        $stateId = $request->get('state_id', null);

        $em = $this->getDoctrine()->getManager();
        $cities = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:City');
        $context = $this->getSerializationContext('typeahead');
        $result = $cities->findByString($city, $countryId, $stateId);

        return $this->renderWithContext($result, $context);
    }

    /**
     * Searches states by name and, optionally, country.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Searches states by name and, optionally, country.",
     *   output = {
     *     "class"="PROCERGS\LoginCidadao\CoreBundle\Entity\State",
     *     "groups" = {"public"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when no city is found"
     *   }
     * )
     * @REST\View(templateVar="states")
     * @param string  $state
     * @return \PROCERGS\LoginCidadao\CoreBundle\Entity\State
     * @throws NotFoundHttpException when no state is found
     * @REST\Get("/address/states/search/{state}", name="api_1_get_states")
     * @Audit\Loggable(type="SELECT")
     */
    public function getStatesAction($state)
    {
        $em = $this->getDoctrine()->getManager();
        $states = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:State');
        $context = $this->getSerializationContext('typeahead');
        $result = $states->findByString($state);

        return $this->renderWithContext($result, $context);
    }

    /**
     * Searches countries by name.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Searches countries by name.",
     *   output = {
     *     "class"="PROCERGS\LoginCidadao\CoreBundle\Entity\Country",
     *     "groups" = {"public"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when no city is found"
     *   }
     * )
     * @REST\View(templateVar="countries")
     * @param string  $country
     * @return \PROCERGS\LoginCidadao\CoreBundle\Entity\Country
     * @throws NotFoundHttpException when no country is found
     * @REST\Get("/address/countries/search/{country}", name="api_1_get_countries")
     * @Audit\Loggable(type="SELECT")
     */
    public function getCountriesAction($country)
    {
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Country');
        $context = $this->getSerializationContext('typeahead');
        $result = $countries->findByString($country);

        return $this->renderWithContext($result, $context);
    }

    /**
     * Searches cities by name and, optionally, state.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Searches cities by name and, optionally, state.",
     *   output = {
     *     "class"="PROCERGS\LoginCidadao\CoreBundle\Entity\City",
     *     "groups" = {"public"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when no city is found"
     *   }
     * )
     * @REST\View(templateVar="cities")
     * @return \PROCERGS\LoginCidadao\CoreBundle\Entity\City
     * @throws NotFoundHttpException when no city is found
     * @REST\Get("/address/cities/prefetch", name="api_1_get_cities_prefetch")
     * @Audit\Loggable(type="SELECT")
     */
    public function getCitiesPrefetchAction()
    {
        $em = $this->getDoctrine()->getManager();
        $cities = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:City')->findByPreferedState();

        return $cities;
    }

}
