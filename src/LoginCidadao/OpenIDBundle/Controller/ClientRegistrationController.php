<?php

namespace LoginCidadao\OpenIDBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as REST;
use LoginCidadao\OpenIDBundle\Form\ClientMetadataForm;
use LoginCidadao\OpenIDBundle\Model\ClientMetadata;
use LoginCidadao\OpenIDBundle\Exception\DynamicRegistrationException;

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
            $error = $this->handleFormErrors($form->getErrors(true));
            return $this->view($error->getData(), 400);
        }
    }

    /**
     * @param \Symfony\Component\Form\FormError[] $errors
     * @return DynamicRegistrationException
     */
    private function handleFormErrors($errors)
    {
        foreach ($errors as $error) {
            $cause    = $error->getCause();
            $value    = $cause->getInvalidValue();
            $property = str_replace('data.', '', $cause->getPropertyPath());

            if (strpos($property, 'redirect_uris') !== false) {
                return new DynamicRegistrationException('Invalid redirect uri: '.$value,
                    DynamicRegistrationException::ERROR_INVALID_REDIRECT_URI);
            } else {
                return new DynamicRegistrationException("Invalid value for '{$property}': {$value}",
                    DynamicRegistrationException::ERROR_INVALID_CLIENT_METADATA);
            }
        }
    }
}
