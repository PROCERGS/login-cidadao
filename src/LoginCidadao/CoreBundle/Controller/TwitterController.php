<?php

namespace LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

class TwitterController extends Controller
{

    /**
     * @Route("/register/twitter", name="lc_before_register_twitter")
     * @Template()
     */
    public function beforeRegisterAction(Request $request)
    {
        $formBuilder = $this->createFormBuilder()
            ->add('email', 'email',
                array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 3)),
                ),
            ))
            ->add('save', 'submit');

        $form = $formBuilder->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();

            $person = $this->getDoctrine()
                ->getRepository('LoginCidadaoCoreBundle:Person')
                ->findByEmail($data['email']);

            if ($person) {
                $formError = new FormError($this->get('translator')->trans('The email is already used'));
                $form->get('email')->addError($formError);

                return array('form' => $form->createView());
            }

            $session = $request->getSession();
            $session->set('twitter.email', $data['email']);

            return $this->redirect($this->generateUrl('hwi_oauth_service_redirect',
                        array('service' => 'twitter')));
        }

        return array('form' => $form->createView());
    }
}
