<?php

namespace LoginCidadao\CoreBundle\Controller;

use LoginCidadao\CoreBundle\Model\SupportMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use LoginCidadao\CoreBundle\Entity\SentEmail;
use LoginCidadao\APIBundle\Entity\LogoutKey;

class DefaultController extends Controller
{

    /**
     * @Route("/help", name="lc_help")
     * @Template()
     */
    public function helpAction(Request $request)
    {
        return $this->render('LoginCidadaoCoreBundle:Info:help.html.twig');
    }

    /**
     * @Route("/contact/{correlationId}", defaults={"correlationId" = null}, name="lc_contact")
     * @Template()
     */
    public function contactAction(Request $request, $correlationId = null)
    {
        $data = new SupportMessage();
        $form = $this->createForm('contact_form_type', $data);
        $form->handleRequest($request);
        $translator = $this->get('translator');
        $message = $translator->trans('contact.form.sent');
        if ($form->isValid()) {
            $emailMessage = $data->getMessage();
            if ($correlationId !== null) {
                $emailMessage = "<p>$emailMessage</p><p>Correlation Id: {$correlationId}</p>";
            }
            $email = new SentEmail();
            $email
                ->setType('contact-mail')
                ->setSubject('Fale conosco - '.$data->getName())
                ->setSender($data->getEmail())
                ->setReceiver($this->container->getParameter('contact_form.email'))
                ->setMessage($emailMessage);
            $mailer = $this->get('mailer');
            $swiftMail = $email->getSwiftMail();
            if ($mailer->send($swiftMail)) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($email);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $message);
            }

            $url = $this->generateUrl("lc_contact");

            return $this->redirect($url);
        }

        return $this->render(
            'LoginCidadaoCoreBundle:Info:contact.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @Route("/dashboard", name="lc_dashboard")
     * @Template()
     */
    public function dashboardAction()
    {
        // badges
        $badgesHandler = $this->get('badges.handler');
        $badges = $badgesHandler->getAvailableBadges();
        $userBadges = $badgesHandler->evaluate($this->getUser())->getBadges();

        // logs
        $em = $this->getDoctrine()->getManager();
        $logRepo = $em->getRepository('LoginCidadaoAPIBundle:ActionLog');
        $logs['logins'] = $logRepo->findLoginsByPerson($this->getUser(), 4);
        $logs['activity'] = $logRepo->getWithClientByPerson($this->getUser(), 3);

        $defaultClientUid = $this->container->getParameter('oauth_default_client.uid');

        return array(
            'allBadges' => $badges,
            'userBadges' => $userBadges,
            'logs' => $logs,
            'defaultClientUid' => $defaultClientUid,
        );
    }

    /**
     * @Route("/logout/if-not-remembered/{key}", name="lc_logout_not_remembered_safe")
     * @Template()
     */
    public function safeLogoutIfNotRememberedAction(Request $request, $key)
    {
        $em = $this->getDoctrine()->getManager();
        $logoutKeys = $em->getRepository('LoginCidadaoAPIBundle:LogoutKey');
        $logoutKey = $logoutKeys->findActiveByKey($key);

        if (!($logoutKey instanceof LogoutKey)) {
            throw new AccessDeniedHttpException("Invalid logout key.");
        }

        $result['logged_out'] = false;
        if ($this->getUser() instanceof UserInterface) {
            if ($request->cookies->has($this->getParameter('session.remember_me.name'))) {
                $result = array('logged_out' => false);
            } else {
                $this->get("request")->getSession()->invalidate();
                $this->get("security.token_storage")->setToken(null);
                $result['logged_out'] = true;
            }
        } else {
            $result['logged_out'] = true;
        }

        $response = new JsonResponse();
        $userAgent = $request->headers->get('User-Agent');
        if (preg_match('/(?i)msie [1-9]/', $userAgent)) {
            $response->headers->set('Content-Type', 'text/json');
        }

        $client = $logoutKey->getClient();
        $em->remove($logoutKey);
        $em->flush();

        $redirectUrl = $request->get('redirect_url');
        if ($redirectUrl !== null) {
            $host = parse_url($redirectUrl, PHP_URL_HOST);
            if ($client->ownsDomain($host)) {
                return $this->redirect($redirectUrl);
            } else {
                $result['error'] = "Invalid redirect_url domain. It doesn't appear to belong to {$client->getName()}";
            }
        }

        return $response->setData($result);
    }

    /**
     * @Route("/_home", name="lc_index")
     * @Template()
     */
    public function indexAction(Request $request, $lastUsername)
    {
        return array(
            'last_username' => $lastUsername,
        );
    }
}
