<?php

namespace LoginCidadao\TOSBundle\Controller;

use LoginCidadao\TOSBundle\Entity\Agreement;
use LoginCidadao\TOSBundle\Form\AgreementType;
use LoginCidadao\TOSBundle\Model\AgreementInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AgreementController extends Controller
{

    /**
     * @Route("/terms/agree", name="tos_agree")
     * @Template()
     */
    public function askAction(Request $request)
    {
        if ($this->agreedToTermsOfService()) {
            return $this->continueNavigation($request);
        }

        $termsRepo = $this->getDoctrine()->getRepository('LoginCidadaoTOSBundle:TermsOfService');
        $latest    = $termsRepo->findLatestTerms();

        $agreement = new Agreement();
        $agreement->setTermsOfService($latest)
            ->setUser($this->getUser());

        $form = $this->getAgreementForm($agreement);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($agreement);
            $em->flush();

            return $this->continueNavigation($request);
        }

        return compact('latest', 'form');
    }

    private function getAgreementForm(AgreementInterface $agreement)
    {
        $form = $this->createForm(new AgreementType(), $agreement,
            array(
            'action' => $this->generateUrl('tos_agree'),
            'method' => 'POST',
            'translation_domain' => 'LoginCidadaoTOSBundle'
        ));
        $form->add('submit', 'submit',
            array(
            'label' => 'tos.agreement.form.button.submit',
            'attr' => array('class' => 'btn-success')
        ));
        return $form;
    }

    private function agreedToTermsOfService()
    {
        $termsRepo     = $this->getDoctrine()->getRepository('LoginCidadaoTOSBundle:TermsOfService');
        $agreementRepo = $this->getDoctrine()->getRepository('LoginCidadaoTOSBundle:Agreement');

        $user      = $this->getUser();
        $latest    = $termsRepo->findLatestTerms();
        $agreement = $agreementRepo->findOneBy(array(
            'user' => $user,
            'termsOfService' => $latest
        ));

        if (!($agreement instanceof AgreementInterface)) {
            return false;
        }

        return ($agreement->getAgreedAt() > $latest->getUpdatedAt());
    }

    private function continueNavigation(Request $request)
    {
        $url = $request->getSession()->get('tos_continue_url', '/');
        $request->getSession()->remove('tos_continue_url');
        return $this->redirect($url);
    }
}
