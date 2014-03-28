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
use PROCERGS\OAuthBundle\Entity\Client;

/**
 * @Route("/admin/apps")
 */
class AdminAppsController extends Controller
{

    /**
     * @Route("/new", name="lc_admin_app_new")
     * @Template()
     */
    public function newAction()
    {
        $client = new Client();
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.client.form.type'), $client);
        $form->handleRequest($this->getRequest());
        $messages = '';
        if ($form->isValid()) {
            $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
            $clientManager->updateClient($client);
            return $this->redirect($this->generateUrl('lc_admin_app_show', array(
                'id' => $client->getId()
            )));
        }
        return array(
            'form' => $form->createView(),
            'messages' => $messages
        );
    }

    /**
     * @Route("/show/{id}", name="lc_admin_app_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('PROCERGSOAuthBundle:Client')->find($id);
        return array(
            'client' => $client
        );
    }

    /**
     * @Route("/list", name="lc_admin_app_list")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $clients = $em->getRepository('PROCERGSOAuthBundle:Client')->findAll();
        return array(
            'clients' => $clients
        );
    }

    /**
     * @Route("/edit/{id}", name="lc_admin_app_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('PROCERGSOAuthBundle:Client')->find($id);
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.client.form.type'), $client);
        $form->handleRequest($this->getRequest());
        $messages = '';
        if ($form->isValid()) {
            $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
            $clientManager->updateClient($client);
            $messages = 'aeee';
        }
        return $this->render('PROCERGSLoginCidadaoCoreBundle:AdminApps:new.html.twig', array(
            'form' => $form->createView(),
            'client' => $client,
            'messages' => $messages
        ));
    }
}
