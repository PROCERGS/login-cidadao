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
        $termsRepo = $this->getDoctrine()->getRepository('LoginCidadaoTOSBundle:TermsOfService');
        $latest    = $termsRepo->findLatestTerms();

        $agreement = new Agreement();
        $agreement->setTermsOfService($latest)
            ->setUser($this->getUser());

        $form = $this->getAgreementForm($agreement);
        $form->handleRequest($request);

        if ($form->isValid()) {
            //
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
}
