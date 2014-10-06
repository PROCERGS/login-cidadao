<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\ContactFormType;
use PROCERGS\LoginCidadao\CoreBundle\Entity\SentEmail;
use PROCERGS\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\CoreBundle\Helper\GridHelper;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Notification;

/**
 * @Route("/admin/client")
 */
class ClientController extends Controller
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
            $client->setPerson($this->getUser());
            $client->setAllowedGrantTypes(Client::getAllGrants());
            $clientManager->updateClient($client);
            return $this->redirect($this->generateUrl('lc_admin_app_edit', array(
                'id' => $client->getId()
            )));
        }
        return array(
            'form' => $form->createView(),
            'messages' => $messages
        );
    }

    /**
     * @Route("/", name="lc_admin_app")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return $this->gridAction($request);
    }

    /**
     * @Route("/grid", name="lc_admin_app_grid")
     * @Template()
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = $em->getRepository('PROCERGSOAuthBundle:Client')
            ->createQueryBuilder('c')
            ->addOrderBy('c.id', 'desc');
        ;
        $grid = new GridHelper();
        $grid->setId('client-grid');
        $grid->setPerPage(5);
        $grid->setMaxResult(5);
        $grid->setQueryBuilder($sql);
        $grid->setInfinityGrid(true);
        $grid->setRoute('lc_admin_app_grid');
        return array(
            'grid' => $grid->createView($request)
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
        if (! $client) {
            return $this->redirect($this->generateUrl('lc_admin_app_new'));
        }
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.client.form.type'), $client);
        $form->handleRequest($this->getRequest());
        $messages = '';
        if ($form->isValid()) {
            $client->setAllowedGrantTypes(Client::getAllGrants());
            $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
            $clientManager->updateClient($client);
            $messages = 'aeee';
        }
        return $this->render('PROCERGSLoginCidadaoCoreBundle:Admin\Client:new.html.twig', array(
            'form' => $form->createView(),
            'client' => $client,
            'messages' => $messages
        ));
    }
    /**
     * @Route("/replicate", name="lc_admin_app_replicate")
     * @Template()
     */
    public function replicateMeAction()
    {
        $id = 1;
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('PROCERGSOAuthBundle:Client')->find($id);
        foreach (range(1, 50) as $var) {
            $newClient = clone $client;
            $newClient->setId(null);
            $rid = uniqid();
            $newClient->setName('clone_'. $rid);
            $newClient->setRandomId($rid);        
            $em->persist($newClient);
            $cats = $client->getCategories();
            foreach ($cats as $cat) {
                $newCat = clone $cat;
                $newCat->setClient($newClient);
                $em->persist($newCat);
                $places = $cat->getPlaceholders();
                foreach ($places as $place) {
                    $newPlace = clone $place;
                    $newPlace->setId(null);
                    $newPlace->setCategory($newCat);
                    $em->persist($newPlace);
                }
                foreach (range(1,100) as $var2) {
                    $newNot = new Notification();
                    $newNot->setCategory($newCat);
                }
            }
        }
        $em->flush();
        return new Response("ok");
    }
}
