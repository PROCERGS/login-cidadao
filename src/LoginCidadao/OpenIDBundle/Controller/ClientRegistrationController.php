<?php

namespace LoginCidadao\OpenIDBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as REST;
use LoginCidadao\OpenIDBundle\Form\ClientMetadataForm;
use LoginCidadao\OpenIDBundle\Model\ClientMetadata;

/**
 * @REST\Route("/openid/connect")
 */
class ClientRegistrationController extends FOSRestController
{

    /**
     * @REST\Post("/register", name="oidc_dynamic_registration", defaults={"_format"="json"})
     * @REST\View(templateVar="client")
     */
    public function registerAction(Request $request)
    {
        $request->setFormat('json', 'application/json');
        if (0 === strpos($request->headers->get('Content-Type'),
                'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }

        $data = new ClientMetadata();
        $form = $this->createForm(new ClientMetadataForm(), $data);

        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->view($form->getData(), 201);
        } else {
            $errors = $form->getErrors();
            var_dump($errors->count());
            var_dump($form);
            die();
        }

        return new JsonResponse(array(
            'error' => 'invalid_client_metadata',
            'error_description' => 'Missing client metadata.'
        ));
    }
}
