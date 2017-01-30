<?php

namespace LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\CoreBundle\Entity\SentEmail;

class JobsController extends Controller
{

    /**
     * @Route("/job/email-cleanup")
     * @Template()
     */
    public function emailCleanupAction()
    {
        $personRepo          = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:Person');
        $missingConfirmation = $personRepo->findUnconfirmedEmailUntilDate(new \DateTime());

        $deleted = array();
        if (!empty($missingConfirmation)) {
            $em = $this->getDoctrine()->getManager();
            foreach ($missingConfirmation as $person) {
                $previous = $person->getPreviousValidEmail();
                if (is_null($previous)) {
                    $deleted[] = $person;
                    $em->remove($person);
                } else {
                    $person->setEmail($previous)
                        ->setEmailConfirmedAt(new \DateTime())
                        ->setEmailExpiration(null)
                        ->setPreviousValidEmail(null)
                        ->setConfirmationToken(null);
                    $this->get('fos_user.user_manager')->updateUser($person);
                }
            }
            $em->flush();
        }

        return compact('deleted');
    }
}
