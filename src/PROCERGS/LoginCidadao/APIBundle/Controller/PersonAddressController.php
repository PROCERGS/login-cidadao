<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     */
    public function getCitiesAction($city)
    {
        $em = $this->getDoctrine()->getManager();
        $cities = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:City');
        $context = $this->getSerializationContext('typeahead');
        $result = $cities->findByString($city);

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
     */
    public function getCitiesPrefetchAction()
    {
        $em = $this->getDoctrine()->getManager();
        $cities = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:City')->findByPreferedState();

        return $cities;
    }

}
