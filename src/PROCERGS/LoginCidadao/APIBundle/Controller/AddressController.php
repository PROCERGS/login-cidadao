<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations as REST;

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

}
