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
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\ClientNotCatFormType;
use Michelf\MarkdownExtra;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Category;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Placeholder;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\PlaceholderFormType;
use PROCERGS\LoginCidadao\CoreBundle\Helper\GridHelper;

/**
 * @Route("/dev/not")
 */
class NotificationController extends Controller
{

    /**
     * @Route("/new", name="lc_dev_not_new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $category = new Category();
        $category->setMailTemplate("%title%\r\n%shorttext%\r\n");
        $category->setMailSenderAddress($this->getUser()->getEmail());
        $category->setEmailable(true);
        $category->setMarkdownTemplate("%title%\r\n--\r\n\r\n> %shorttext%\r\n\r\n");
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.category.form.type'),
                                                                                    $category);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($category);
            $manager->flush();
            return $this->redirect($this->generateUrl('lc_dev_not_edit',
                                                      array(
                        'id' => $category->getId()
            )));
        }
        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/", name="lc_dev_not")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return $this->gridAction($request);
    }

    /**
     * @Route("/grid", name="lc_dev_not_grid")
     * @Template()
     */
    public function gridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = $this->getDoctrine()->getManager()
            ->getRepository('PROCERGSLoginCidadaoNotificationBundle:Category')
            ->createQueryBuilder('u')
            ->join('PROCERGSOAuthBundle:Client', 'c', 'with', 'u.client = c')
            ->where(':person MEMBER OF c.owners')
            ->setParameter('person', $this->getUser())
            ->orderBy('u.id', 'desc');
        $grid = new GridHelper();
        $grid->setId('category-grid');
        $grid->setPerPage(5);
        $grid->setMaxResult(5);
        $grid->setQueryBuilder($sql);
        $grid->setInfiniteGrid(true);
        $grid->setRoute('lc_dev_not_grid');
        return array('grid' => $grid->createView($request));
    }

    /**
     * @Route("/edit/{id}", name="lc_dev_not_edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('PROCERGSLoginCidadaoNotificationBundle:Category')
            ->createQueryBuilder('u')
            ->join('PROCERGSOAuthBundle:Client', 'c', 'with', 'u.client = c')
            ->where(':person MEMBER OF c.owners')
            ->andWhere('u.id = :id')
            ->setParameter('person', $this->getUser())
            ->setParameter('id', $id)
            ->orderBy('u.id', 'desc')
            ->getQuery()
            ->getOneOrNullResult();
        if (!$client) {
            return $this->redirect($this->generateUrl('lc_dev_not'));
        }
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.category.form.type'),
                                                                                    $client);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $client->setHtmlTemplate(MarkdownExtra::defaultTransform($form->get('markdownTemplate')->getData()));
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($client);
            $manager->flush();
        }
        $request = $request;
        $request->query->set('category_id', $id);
        $placeholders = $this->placeholderGridAction($request);
        return $this->render('PROCERGSLoginCidadaoCoreBundle:Dev\Notification:new.html.twig',
                             array(
                'form' => $form->createView(),
                'client' => $client,
                'placeholderGrid' => $placeholders['grid']
        ));
    }

    /**
     * @Route("/placeholder/edit", name="lc_dev_not_placeholder_edit")
     * @Template()
     */
    public function placeholderEditAction(Request $request)
    {
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.placeholder.form.type'));
        $placeholder = null;
        $em = $this->getDoctrine()->getManager();
        if (($id = $request->get('id')) || (($data = $request->get($form->getName())) && ($id = $data['id']))) {
            $placeholder = $em->getRepository('PROCERGSLoginCidadaoNotificationBundle:Placeholder')
                ->createQueryBuilder('u')
                ->join('PROCERGSLoginCidadaoNotificationBundle:Category',
                       'cat', 'with', 'u.category = cat')
                ->join('PROCERGSOAuthBundle:Client', 'c', 'with',
                       'cat.client = c')
                ->where(':person MEMBER OF c.owners')
                ->andWhere('u.id = :id')
                ->setParameter('person', $this->getUser())
                ->setParameter('id', $id)
                ->orderBy('u.id', 'desc')
                ->getQuery()
                ->getSingleResult();
        } elseif (($categoryId = $request->get('category_id')) || (($data = $request->get($form->getName())) && ($categoryId = $data['category']))) {
            $category = $em->getRepository('PROCERGSLoginCidadaoNotificationBundle:Category')
                ->createQueryBuilder('u')
                ->join('PROCERGSOAuthBundle:Client', 'c', 'with', 'u.client = c')
                ->where(':person MEMBER OF c.owners')
                ->andWhere('u.id = :id')
                ->setParameter('person', $this->getUser())
                ->setParameter('id', $categoryId)
                ->orderBy('u.id', 'desc')
                ->getQuery()
                ->getSingleResult();
            $placeholder = new Placeholder();
            $placeholder->setCategory($category);
        }
        if (!$placeholder) {
            die('dunno');
        }
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.placeholder.form.type'),
                                                                                    $placeholder);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($placeholder);
            $em->flush();
            $resp = new Response('<script>placeholderGrid.getGrid();</script>');
            return $resp;
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/placeholder/grid", name="lc_dev_not_placeholder_grid")
     * @Template()
     */
    public function placeholderGridAction(Request $request)
    {
        $categoryId = $request->get('category_id');
        $em = $this->getDoctrine()->getManager();
        $sql = $em->getRepository('PROCERGSLoginCidadaoNotificationBundle:Placeholder')
            ->createQueryBuilder('u')
            ->join('PROCERGSLoginCidadaoNotificationBundle:Category',
                   'cat', 'with', 'u.category = cat')
            ->join('PROCERGSOAuthBundle:Client', 'c', 'with', 'cat.client = c')
            ->where(':person MEMBER OF c.owners')
            ->andWhere('cat.id = :id')
            ->setParameter('person', $this->getUser())
            ->setParameter('id', $categoryId)
            ->orderBy('u.id', 'desc');

        $grid = new GridHelper();
        $grid->setId('placeholder-grid');
        $grid->setPerPage(2);
        $grid->setMaxResult(2);
        $grid->setQueryBuilder($sql);
        $grid->setInfiniteGrid(true);
        $grid->setRoute('lc_dev_not_placeholder_grid');
        $grid->setRouteParams(array('category_id'));
        return array('grid' => $grid->createView($request));
    }

    /**
     * @Route("/placeholder/remove", name="lc_dev_not_placeholder_remove")
     * @Template()
     */
    public function placeholderRemoveAction(Request $request)
    {
        if ($id = $request->get('id')) {
            $em = $this->getDoctrine()->getManager();
            $placeholder = $em->getRepository('PROCERGSLoginCidadaoNotificationBundle:Placeholder')
                ->createQueryBuilder('u')
                ->join('PROCERGSLoginCidadaoNotificationBundle:Category',
                       'cat', 'with', 'u.category = cat')
                ->join('PROCERGSOAuthBundle:Client', 'c', 'with',
                       'cat.client = c')
                ->where(':person MEMBER OF c.owners')
                ->andWhere('u.id = :id')
                ->setParameter('person', $this->getUser())
                ->setParameter('id', $id)
                ->orderBy('u.id', 'desc')
                ->getQuery()
                ->getOneOrNullResult();
            if ($placeholder) {
                $em->remove($placeholder);
                $em->flush();
            }
        }
        $resp = new Response('<script>placeholderGrid.getGrid();</script>');
        return $resp;
    }

}
