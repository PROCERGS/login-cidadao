<?php

namespace LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\CoreBundle\Entity\SentEmail;

class JobsController extends Controller
{

    /**
     * @Route("/job/cpf-reminder")
     * @Template()
     */
    public function cpfCheckAction()
    {
        $mailType   = 'cpf-reminder';
        $translator = $this->get('translator');

        $subject = $translator->trans('cpf_reminder.subject');

        $from       = $this->container->getParameter('mailer_sender_mail');
        $em         = $this->getDoctrine()->getManager();
        $personRepo = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:Person');
        $emailRepo  = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:SentEmail');

        $users     = $personRepo->findAllPendingCPFUntilDate(new \DateTime());
        $todayMail = $emailRepo->findAllSentInTheLast24h($mailType, true);

        $mailCount = 0;
        foreach ($users as $user) {
            $to = $user->getEmailCanonical();
            if (array_key_exists($to, $todayMail)) {
                continue;
            }

            $email = new SentEmail();
            $email->setType($mailType)
                ->setSubject($subject)
                ->setSender($from)
                ->setReceiver($to)
                ->setMessage($this->renderView('LoginCidadaoCoreBundle:Jobs:cpf_check.html.twig',
                        compact('user')));

            if ($this->get('mailer')->send($email->getSwiftMail())) {
                $em->persist($email);
                $em->flush();
                $mailCount++;
            }
        }

        return compact('mailCount');
    }

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
