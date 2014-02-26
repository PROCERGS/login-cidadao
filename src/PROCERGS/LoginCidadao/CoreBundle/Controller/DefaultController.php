<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\DisabledException;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\LoginFormType;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Login;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="lc_home")
     * @Template()
     */
    public function indexAction()
    {
        $security = $this->get('security.context');
        if (false === $security->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('fos_user_registration_register'));
        } else {
            $em = $this->getDoctrine()->getManager();
            $clients = $em->getRepository('PROCERGSOAuthBundle:Client');

            $user = $security->getToken()->getUser();
            $allApps = $clients->findAll();

            $apps = array();
            // Filtering off authorized apps
            foreach ($allApps as $app) {
                if ($user->hasAuthorization($app)) {
                    continue;
                }
                $apps[] = $app;
            }

            return $this->render(
                'PROCERGSLoginCidadaoCoreBundle:Default:index.loggedIn.html.twig',
                compact('user', 'apps')
            );
        }
    }

    /**
     * @Route("/failure_login", name="lc_home_failure_login")
     * @Template()
     */
    public function failureLoginAction(Request $request)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }
        if ($error && $error instanceof DisabledException) {
            $person = $error->getUser();
            if ($person->getConfirmationToken() !== null) {
                if (null !== $session) {
                    $session->remove(SecurityContext::AUTHENTICATION_ERROR);
                }
                return $this->render('PROCERGSLoginCidadaoCoreBundle:Person:index.checkEmail.html.twig', compact('person'));
            }
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

        /**
     * @Route("/apps", name="lc_apps")
     * @Template()
     */
    public function appsAction(Request $request)
    {
        $security = $this->get('security.context');
        if (false === $security->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('fos_user_registration_register'));
        } else {
            $em = $this->getDoctrine()->getManager();
            $clients = $em->getRepository('PROCERGSOAuthBundle:Client');

            $user = $security->getToken()->getUser();
            $allApps = $clients->findAll();

            $apps = array();
            // Filtering off authorized apps
            foreach ($allApps as $app) {
                if ($user->hasAuthorization($app)) {
                    continue;
                }
                $apps[] = $app;
            }

            return $this->render(
                'PROCERGSLoginCidadaoCoreBundle:Person:apps.html.twig',
                compact('user', 'apps')
            );
        }
    }
}