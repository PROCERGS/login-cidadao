<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Controller\Dev;

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
 * @Route("/dev/client")
 */
class ClientController extends Controller
{

    /**
     * @Route("/new", name="lc_dev_client_new")
     * @Template()
     */
    public function newAction()
    {
        $client = new Client();
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.client.base.form.type'), $client);
        
        $form->handleRequest($this->getRequest());
        $messages = '';
        if ($form->isValid()) {
            $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
            $client->setPerson($this->getUser());
            $clientManager->updateClient($client);
            return $this->redirect($this->generateUrl('lc_dev_client_edit', array(
                'id' => $client->getId()
            )));
        }
        return array(
            'form' => $form->createView(),
            'messages' => $messages
        );
    }

    /**
     * @Route("/list", name="lc_dev_client_list")
     * @Template()
     */
    public function listAction()
    {
        return $this->gridAction();
    }
    
    /**
     * @Route("/grid", name="lc_dev_client_grid")
     * @Template()
     */
    public function gridAction()
    {
        $em = $this->getDoctrine()->getManager();
        $clients = $em->getRepository('PROCERGSOAuthBundle:Client')->findBy(array('person' => $this->getUser()), array(
            'id' => 'desc'
        ));
        return array(
            'resultset' => $clients
        );
    }
    

    /**
     * @Route("/edit/{id}", name="lc_dev_client_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('PROCERGSOAuthBundle:Client')->findOneBy(array('id' => $id, 'person' => $this->getUser()));
        if (!$client) {
            return $this->redirect($this->generateUrl('lc_dev_client_new'));
        }
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.client.base.form.type'), $client);
        $form->handleRequest($this->getRequest());
        $messages = '';
        if ($form->isValid()) {
            $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
            $clientManager->updateClient($client);
            $messages = 'aeee';
        }
        return $this->render('PROCERGSLoginCidadaoCoreBundle:Dev\Client:new.html.twig',
                        array(
                    'form' => $form->createView(),
                    'client' => $client,
                    'messages' => $messages
        ));
    }
    
}
