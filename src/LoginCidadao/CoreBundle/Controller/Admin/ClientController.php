<?php

namespace LoginCidadao\CoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\CoreBundle\Helper\GridHelper;
use Michelf\MarkdownExtra;

/**
 * @Route("/admin/client")
 */
class ClientController extends Controller
{

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
        $em   = $this->getDoctrine()->getManager();
        $sql  = $em->getRepository('LoginCidadaoOAuthBundle:Client')
            ->createQueryBuilder('c')
            ->addOrderBy('c.id', 'desc');
        ;
        $grid = new GridHelper();
        $grid->setId('client-grid');
        $grid->setPerPage(15);
        $grid->setMaxResult(15);
        $grid->setQueryBuilder($sql);
        $grid->setInfiniteGrid(true);
        $grid->setRoute('lc_admin_app');
        return array(
            'grid' => $grid->createView($request)
        );
    }

    /**
     * @Route("/{id}/edit", name="lc_admin_app_edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $em     = $this->getDoctrine()->getManager();
        $client = $em->getRepository('LoginCidadaoOAuthBundle:Client')->find($id);
        if (!$client) {
            return $this->redirect($this->generateUrl('lc_admin_app_new'));
        }
        $form = $this->createForm('LoginCidadao\CoreBundle\Form\Type\ClientFormType',
            $client);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $metadata = $form->get('metadata')->getData();
            $client->setAllowedGrantTypes(Client::getAllGrants());
            $client->setMetadata($metadata);
            $metadata->setClient($client);

            $clientManager = $this->container->get('fos_oauth_server.client_manager');
            $clientManager->updateClient($client);

            $translator = $this->get('translator');
            $this->get('session')->getFlashBag()->add('success',
                $translator->trans('Updated successfully!'));

            return $this->redirectToRoute('lc_admin_app_edit', compact('id'));
        }
        return $this->render('LoginCidadaoCoreBundle:Admin\Client:new.html.twig',
                array(
                'form' => $form->createView(),
                'client' => $client
        ));
    }
}
