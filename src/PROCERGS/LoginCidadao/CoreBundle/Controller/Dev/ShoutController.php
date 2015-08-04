<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller\Dev;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\ContactFormType;
use PROCERGS\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\CoreBundle\Helper\GridHelper;

/**
 * @Route("/dev/shout")
 */
class ShoutController extends Controller
{

    /**
     * @Route("/new", name="lc_dev_shout_new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        return $this->stepCategoryAction($request);
    }

    /**
     * @Route("/step/category", name="lc_dev_shout_step_category")
     * @Template()
     */
    public function stepCategoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = $em->getRepository('PROCERGSLoginCidadaoNotificationBundle:Category')
            ->getOwnedCategoriesQuery($this->getUser());

        $grid = new GridHelper();
        $grid->setId('shout-category-grid');
        $grid->setPerPage(5);
        $grid->setMaxResult(5);
        $grid->setQueryBuilder($sql);
        $grid->setInfiniteGrid(true);
        $grid->setRoute('lc_dev_shout_step_category');
        return array(
            'grid' => $grid->createView($request)
        );
    }

    /**
     * @Route("/step/placeholder", name="lc_dev_shout_step_placeholder")
     * @Template()
     */
    public function stepPlaceholderAction(Request $request)
    {
        $categoryId = $request->get('id');
        if (!$categoryId) {
            die('dunno');
        }
        $em = $this->getDoctrine()->getManager();
        $placeholders = $em->getRepository('PROCERGSLoginCidadaoNotificationBundle:Placeholder')
            ->findOwnedPlaceholdersByCategoryId($this->getUser(), $categoryId);

        $form = $this->createFormBuilder();
        foreach ($placeholders as $placeholder) {
            $form->add('place_' . $placeholder->getId(), 'text',
                       array(
                'label' => $placeholder->getName(),
                'data' => $placeholder->getDefault()
            ));
        }
        $form->add('id', 'hidden',
                   array(
            'data' => $categoryId
        ));
        $form->add('owners', 'ajax_choice',
                   array(
            'attr' => array(
                'data-ac-route' => $this->generateUrl('lc_dev_shout_step_category'),
                'data-ac-search-prop' => 'name',
                'data-ac-extra-prop' => json_encode(array($form->get('id')->getName())),
            ),
            'multiple' => true
        ));
        $form = $form->getForm();
        return array('form' => $form->createView());
    }

    /**
     * @Route("/", name="lc_dev_shout")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return $this->gridAction($request);
    }

    /**
     * @Route("/grid", name="lc_dev_shout_grid")
     * @Template()
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification\Shout')
            ->createQueryBuilder('c')
            ->where('c.person = :person')
            ->setParameter('person', $this->getUser())
            ->addOrderBy('c.id', 'desc');
        $grid = new GridHelper();
        $grid->setId('shout-grid');
        $grid->setPerPage(5);
        $grid->setMaxResult(5);
        $grid->setQueryBuilder($sql);
        $grid->setInfiniteGrid(true);
        $grid->setRoute('lc_dev_shout_grid');
        return array(
            'grid' => $grid->createView($request)
        );
    }

    /**
     * @Route("/edit/{id}", name="lc_dev_shout_edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('PROCERGSOAuthBundle:Client')
            ->findOneOwned($this->getUser(), $id);
        if (!$client) {
            return $this->redirect($this->generateUrl('lc_dev_shout_new'));
        }
        $form = $this->createForm('procergs_logincidadao.client.base.form.type',
                                  $client);
        $form->handleRequest($request);
        $messages = '';
        if ($form->isValid()) {
            $client->setAllowedGrantTypes(Client::getAllGrants());
            $clientManager = $this->container->get('fos_oauth_server.client_manager');
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
