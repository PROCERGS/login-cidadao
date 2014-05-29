<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations as REST;

class AddressController extends FOSRestController
{
    /**
     * @REST\Get("/lc_consultaCep2", name="lc_consultaCep2")
     * @REST\View()
     */
    public function consultaCep2Action(Request $request)
    {
        $busca = $this->get('procergs_logincidadao.dne');
        $ceps = $busca->findByCep($request->get('cep'));
        if ($ceps) {
            $result = array(
                'code' => 0,
                'msg' => null,
                'items' => array(
                    $ceps
                )
            );
        } else {
            throw new NotFoundHttpException();
        }
        return $result;
    }

}
