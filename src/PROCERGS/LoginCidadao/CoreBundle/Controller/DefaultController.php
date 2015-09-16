<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\ContactFormType;
use PROCERGS\LoginCidadao\CoreBundle\Entity\SentEmail;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use PROCERGS\LoginCidadao\CoreBundle\Helper\IgpWsHelper;
use Doctrine\ORM\Query;
use PROCERGS\LoginCidadao\APIBundle\Entity\LogoutKey;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DefaultController extends Controller
{

    /**
     * @Route("/login/facebook", name="lc_link_facebook")
     */
    public function facebookLoginAction(Request $request)
    {
        $shouldLogout = $request->get('logout');
        if (!is_null($shouldLogout)) {
            $this->get('session')->set('facebook.logout', true);
        }

        $api = $this->container->get('fos_facebook.api');
        $scope = implode(',',
                         $this->container->getParameter('facebook_app_scope'));
        $callback = $this->container->get('router')->generate('_security_check_facebook',
                                                              array(), true);
        $redirect_url = $api->getLoginUrl(array(
            'scope' => $scope,
            'redirect_uri' => $callback
        ));

        return new RedirectResponse($redirect_url);
    }

    /**
     * @Route("/lc_home_gateway", name="lc_home_gateway")
     * @Template()
     */
    public function gatewayAction()
    {
        return array(
            'home1' => $this->generateUrl('lc_home', array(), true)
        );
    }

    /**
     * @Route("/general", name="lc_general")
     * @Template()
     */
    public function generalAction()
    {
        return $this->render('PROCERGSLoginCidadaoCoreBundle:Info:terms.html.twig',
                             compact('user', 'apps'));
    }

    /**
     * @Route("/help", name="lc_help")
     * @Template()
     */
    public function helpAction(Request $request)
    {
        return $this->render('PROCERGSLoginCidadaoCoreBundle:Info:help.html.twig');
    }

    /**
     * @Route("/contact", name="lc_contact")
     * @Template()
     */
    public function contactAction(Request $request)
    {
        $form = $this->createForm(new ContactFormType());
        $form->handleRequest($request);
        $translator = $this->get('translator');
        $message = $translator->trans('contact.form.sent');
        if ($form->isValid()) {
            $email = new SentEmail();
            $email
                ->setType('contact-mail')
                ->setSubject('Fale conosco - ' . $form->get('firstName')->getData())
                ->setSender($form->get('email')->getData())
                ->setReceiver($this->container->getParameter('mailer_receiver_mail'))
                ->setMessage($form->get('message')->getData());
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
        return $this->render('PROCERGSLoginCidadaoCoreBundle:Info:contact.html.twig',
                             array(
                'form' => $form->createView()
        ));
    }

    /**
     * @Route("/logout/if-not-remembered", name="lc_logout_not_remembered")
     * @Template()
     */
    public function logoutIfNotRememberedAction(Request $request)
    {
        $result['logged_out'] = false;
        if ($this->getUser() instanceof UserInterface) {
            if ($request->cookies->has('REMEMBERME')) {
                $result = array('logged_out' => false);
            } else {
                $this->get("request")->getSession()->invalidate();
                $this->get("security.context")->setToken(null);
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
        return $response->setData($result);
    }

    /**
     * @Route("/login/cert", name="lc_login_cert")
     * @Template()
     */
    public function loginCertAction(Request $request)
    {
        die(print_r($_REQUEST));
    }

    /**
     * @Route("/igp/consult", name="lc_ipg_consutl")
     * @Template()
     */
    public function igpConsultAction(Request $request)
    {
        $igp = $this->get('procergs_logincidadao.igpws');
        //$rcs = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoCoreBundle:Person')->createQueryBuilder('p')->select('p.cpf')->where('p.cpf is not null')->getQuery()->getResult(Query::HYDRATE_ARRAY);
        echo "<pre>";
        $rcs[] = array('cpf' => '1000450741');
        foreach ($rcs as $rc) {
            $igp->setCpf($rc['cpf']);
            $rc['igp'] = $igp->consultar();
            print_r($rc);
        }
        die();
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
      $logRepo = $em->getRepository('PROCERGSLoginCidadaoAPIBundle:ActionLog');
      $logs['logins'] = $logRepo->findLoginsByPerson($this->getUser(), 3);
      $logs['activity'] = $logRepo->getWithClientByPerson($this->getUser(), 3);

      // notifications
      $notificationHandler = $this->get('procergs.notification.handler');
      $notifications = $notificationHandler->getUnread($this->getUser());

      $defaultClientUid = $this->container->getParameter('oauth_default_client.uid');

      return array('allBadges' => $badges,
                   'userBadges' => $userBadges,
                   'logs' => $logs,
                   'notifications' => $notifications,
                   'defaultClientUid' => $defaultClientUid);
    }

    /**
     * @Route("/logout/if-not-remembered/{key}", name="lc_logout_not_remembered_safe")
     * @Template()
     */
    public function safeLogoutIfNotRememberedAction(Request $request, $key)
    {
        $em = $this->getDoctrine()->getManager();
        $logoutKeys = $em->getRepository('PROCERGSLoginCidadaoAPIBundle:LogoutKey');
        $logoutKey = $logoutKeys->findActiveByKey($key);

        if (! ($logoutKey instanceof LogoutKey)) {
            throw new AccessDeniedHttpException("Invalid logout key.");
        }

        $result['logged_out'] = false;
        if ($this->getUser() instanceof UserInterface) {
            if ($request->cookies->has('REMEMBERME')) {
                $result = array(
                    'logged_out' => false
                );
            } else {
                $this->get("request")
                    ->getSession()
                    ->invalidate();
                $this->get("security.context")->setToken(null);
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

        $em->remove($logoutKey);
        $em->flush();

        return $response->setData($result);
    }

}
