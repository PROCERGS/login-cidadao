<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\DisabledException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\SentEmail;

class JobsController extends Controller
{

    /**
     * @Route("/job/cpf-reminder")
     * @Template()
     */
    public function cpfCheckAction()
    {
        $translator = $this->get('translator');

        $subject = $translator->trans('cpf_reminder.subject');

        $from = $this->container->getParameter('mailer_sender_mail');
        $em = $this->getDoctrine()->getEntityManager();
        $personRepo = $this->getDoctrine()
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:Person');
        $emailRepo = $this->getDoctrine()
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:SentEmail');

        $users = $personRepo->findAllPendingCPFUntilDate(new \DateTime());
        $todayMail = $emailRepo->findAllSentInTheLast24h(true);

        $mailCount = 0;
        foreach ($users as $user) {
            $to = $user->getEmailCanonical();
            if (array_key_exists($to, $todayMail)) {
                continue;
            }

            $email = new SentEmail();
            $email->setType('cpf-reminder')
                ->setSubject($subject)
                ->setSender($from)
                ->setReceiver($to)
                ->setMessage($this->renderView('PROCERGSLoginCidadaoCoreBundle:Jobs:cpf_check.txt.twig', compact('user')));

            if ($this->get('mailer')->send($email->getSwiftMail())) {
                $em->persist($email);
                $em->flush();
                $mailCount++;
            }
        }

        return compact('mailCount');
    }

}