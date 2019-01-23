<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Controller;

use LoginCidadao\APIBundle\Entity\ActionLogRepository;
use LoginCidadao\BadgesControlBundle\Handler\BadgesHandler;
use LoginCidadao\CoreBundle\Form\Type\ContactFormType;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Model\SupportMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\CoreBundle\Entity\SentEmail;
use LoginCidadao\APIBundle\Entity\LogoutKey;
use Symfony\Component\Translation\TranslatorInterface;

class DefaultController extends Controller
{

    /**
     * @Route("/help", name="lc_help")
     * @Template()
     */
    public function helpAction()
    {
        return $this->render('LoginCidadaoCoreBundle:Info:help.html.twig');
    }

    /**
     * @Route("/contact/{correlationId}", defaults={"correlationId" = null}, name="lc_contact")
     * @Template()
     * @codeCoverageIgnore
     */
    public function contactAction(Request $request, $correlationId = null)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        $person = $this->getUser() instanceof PersonInterface ? $this->getUser() : null;

        $data = new SupportMessage($person);
        $data->setExtra('Correlation Id', $correlationId);

        $form = $this->createForm(ContactFormType::class, $data, [
            'loggedIn' => $person instanceof PersonInterface,
            'recaptchaError' => $translator->trans('contact.form.captcha.error'),
        ]);
        $form->handleRequest($request);

        $message = $translator->trans('contact.form.sent');

        if ($form->isValid()) {
            $email = $this->getEmail($data, $translator);
            $swiftMail = $email->getSwiftMail();
            if ($this->get('mailer')->send($swiftMail)) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($email);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $message);
            }

            return $this->redirectToRoute('lc_contact');
        }

        return $this->render('LoginCidadaoCoreBundle:Info:contact.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/dashboard", name="lc_dashboard")
     * @Template()
     */
    public function dashboardAction()
    {
        // badges
        /** @var BadgesHandler $badgesHandler */
        $badgesHandler = $this->get('badges.handler');
        $badges = $badgesHandler->getAvailableBadges();
        $userBadges = $badgesHandler->evaluate($this->getUser())->getBadges();

        // logs
        $em = $this->getDoctrine()->getManager();

        /** @var ActionLogRepository $logRepo */
        $logRepo = $em->getRepository('LoginCidadaoAPIBundle:ActionLog');
        $logs['logins'] = $logRepo->findLoginsByPerson($this->getUser(), 5);
        $logs['activity'] = $logRepo->getActivityLogsByTarget($this->getUser(), 4);

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
            throw new AccessDeniedException("Invalid logout key.");
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
        ['last_username' => $lastUsername];
    }

    private function getEmail(SupportMessage $supportMessage, TranslatorInterface $translator)
    {
        $message = $supportMessage->getFormattedMessage($translator);

        $email = (new SentEmail())
            ->setType('contact-mail')
            ->setSubject('Fale conosco - '.$supportMessage->getName())
            ->setSender($supportMessage->getEmail())
            ->setReceiver($this->container->getParameter('contact_form.email'))
            ->setMessage($message);

        return $email;
    }
}
