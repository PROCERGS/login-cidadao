<?php

namespace LoginCidadao\CoreBundle\Controller\Dev;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\CoreBundle\Helper\GridHelper;

/**
 * @Route("/dev/client")
 */
class ClientController extends Controller
{

    /**
     * @Route("/new", name="lc_dev_client_new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $client = new Client();
        $form   = $this->createForm('LoginCidadao\CoreBundle\Form\Type\ClientFormType',
            $client);

        $form->handleRequest($request);
        $messages = '';
        if ($form->isValid()) {
            $clientManager = $this->container->get('fos_oauth_server.client_manager');
            $client->getOwners()->add($this->getUser());
            $client->setAllowedGrantTypes(Client::getAllGrants());
            $em            = $this->getDoctrine()->getManager();
            $em->persist($client);
            $em->flush();
            return $this->redirect($this->generateUrl('lc_dev_client_edit',
                        array(
                        'id' => $client->getId()
            )));
        }
        return array(
            'form' => $form->createView(),
            'messages' => $messages
        );
    }

    /**
     * @Route("/", name="lc_dev_client")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return $this->gridAction($request);
    }

    /**
     * @Route("/grid", name="lc_dev_client_grid")
     * @Template()
     */
    public function gridAction(Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $sql  = $em->getRepository('LoginCidadaoOAuthBundle:Client')->createQueryBuilder('c')
            ->where(':person MEMBER OF c.owners')
            ->setParameter('person', $this->getUser())
            ->addOrderBy('c.id', 'desc');
        $grid = new GridHelper();
        $grid->setId('client-grid');
        $grid->setPerPage(5);
        $grid->setMaxResult(5);
        $grid->setQueryBuilder($sql);
        $grid->setInfiniteGrid(true);
        $grid->setRoute('lc_dev_client_grid');
        return array('grid' => $grid->createView($request));
    }

    /**
     * @Route("/grid/developer/filter", name="lc_dev_client_grid_developer_filter")
     * @Template()
     */
    public function gridDeveloperFilterAction(Request $request)
    {
        $grid = new GridHelper();
        $grid->setId('developer-filter-grid');
        $grid->setPerPage(5);
        $grid->setMaxResult(5);
        $parms = $request->get('ac_data');
        if (isset($parms['username'])) {
            $em  = $this->getDoctrine()->getManager();
            $sql = $em->getRepository('LoginCidadaoCoreBundle:Person')->createQueryBuilder('u');
            $sql->select('u');
            $sql->where('1=1');
            $sql->andWhere('u.cpf like ?1 or u.username like ?1 or u.email like ?1 or u.firstName like ?1 or u.surname like ?1');
            $sql->setParameter('1',
                '%'.addcslashes($parms['username'], '\\%_').'%');
            $sql->addOrderBy('u.id', 'desc');
            $grid->setQueryBuilder($sql);
        }
        $grid->setInfiniteGrid(true);
        $grid->setRouteParams(array('ac_data'));
        $grid->setRoute('lc_dev_client_grid_developer_filter');
        return array('grid' => $grid->createView($request));
    }

    /**
     * @Route("/grid/developer", name="lc_dev_client_grid_developer")
     * @Template()
     */
    public function gridDeveloperAction(Request $request)
    {
        $grid = new GridHelper();
        $grid->setId('developer-grid');
        $grid->setPerPage(5);
        $grid->setMaxResult(5);
        $parms = $request->get('ac_data');
        if (isset($parms['person_id']) && !empty($parms['person_id'])) {
            $em  = $this->getDoctrine()->getManager();
            $sql = $em->getRepository('LoginCidadaoCoreBundle:Person')->createQueryBuilder('p');
            $sql->where('p.id in(:id)')->setParameter('id', $parms['person_id']);
            $sql->addOrderBy('p.id', 'desc');
            $grid->setQueryBuilder($sql);
        }
        $grid->setInfiniteGrid(true);
        $grid->setRouteParams(array('ac_data'));
        $grid->setRoute('lc_dev_client_grid_developer');
        return array('grid' => $grid->createView($request));
    }

    /**
     * @Route("/{id}/edit", name="lc_dev_client_edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $em     = $this->getDoctrine()->getManager();
        $client = $em->getRepository('LoginCidadaoOAuthBundle:Client')->createQueryBuilder('c')
                ->where(':person MEMBER OF c.owners')
                ->andWhere('c.id = :id')
                ->setParameters(array('id' => $id, 'person' => $this->getUser()))
                ->getQuery()->getOneOrNullResult();
        if (!$client) {
            return $this->redirect($this->generateUrl('lc_dev_client_new'));
        }

        $form     = $this->createForm('LoginCidadao\CoreBundle\Form\Type\ClientFormType',
            $client);
        $form->handleRequest($request);
        $messages = '';
        if ($form->isValid()) {
            $metadata = $form->get('metadata')->getData();
            $client->setAllowedGrantTypes(Client::getAllGrants());
            $client->setMetadata($metadata);
            $metadata->setClient($client);

            $clientManager = $this->container->get('fos_oauth_server.client_manager');
            $clientManager->updateClient($client);
            $translator    = $this->get('translator');
            $this->get('session')->getFlashBag()->add('success',
                $translator->trans('Updated successfully!'));

            return $this->redirectToRoute('lc_dev_client_edit', compact('id'));
        }
        return $this->render('LoginCidadaoCoreBundle:Dev\Client:new.html.twig',
                array(
                'form' => $form->createView(),
                'client' => $client,
                'messages' => $messages
        ));
    }
}
