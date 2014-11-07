<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\TwoFactorAuthenticationFormType;

/**
 * @Route("/two-factor")
 */
class TwoFactorAuthenticationController extends Controller
{

    /**
     * @Route("/enable")
     * @Template()
     */
    public function enableAction(Request $request)
    {
        $twoFactor = $this->get("scheb_two_factor.security.google_authenticator");
        $person = $this->getPerson();
        $secret = $twoFactor->generateSecret();
        $person->setGoogleAuthenticatorSecret($secret);

        $form = $this->createForm(new TwoFactorAuthenticationFormType(), $person);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $person = $form->getData();
            $em->persist($person);
            $em->flush();
        }

        return array(
            'form' => $form->createView(),
            'secretUrl' => $twoFactor->getUrl($person)
        );
    }

    /**
     * @Route("/disable")
     * @Template()
     */
    public function disableAction()
    {
        return array();
    }

    /**
     * @Route("/teste")
     * @Template()
     */
    public function formAction()
    {
        return array();
    }

    /**
     * @return PersonInterface
     */
    protected function getPerson()
    {
        return $this->getUser();
    }

}
