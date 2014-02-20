<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\DisabledException;

class JobsController extends Controller
{

    /**
     * @Route("/job/cpf-check")
     * @Template()
     */
    public function cpfCheckAction()
    {
        $translator = $this->get('translator');

        $subject = $translator->trans('cpf_reminder.subject');

        $from = $this->container->getParameter('mailer_sender_mail');
        $repo = $this->getDoctrine()
            ->getManager()
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:Person');
        $users = $repo->findAllPendingCPFUntilDate(new \DateTime());

        $mailCount = 0;
        foreach ($users as $user) {
            $message = \Swift_Message::newInstance()->setSubject($subject)
                ->setFrom($from)
                ->setTo($user->getEmailCanonical())
                ->setBody($this->renderView('PROCERGSLoginCidadaoCoreBundle:Jobs:cpf_check.txt.twig', compact('user')));

            if ($this->get('mailer')->send($message)) {
                $mailCount++;
            }
        }

        return compact('mailCount');
    }

}