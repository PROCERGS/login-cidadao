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
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Notification;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Category;
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
        $sql  = $em->getRepository('PROCERGSOAuthBundle:Client')
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
        $client = $em->getRepository('PROCERGSOAuthBundle:Client')->find($id);
        if (!$client) {
            return $this->redirect($this->generateUrl('lc_admin_app_new'));
        }
        $form = $this->createForm('client_form_type', $client);
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
        return $this->render('PROCERGSLoginCidadaoCoreBundle:Admin\Client:new.html.twig',
                array(
                'form' => $form->createView(),
                'client' => $client
        ));
    }

    /**
     * @Route("/populate/{id}", name="lc_admin_app_populate")
     * @Template()
     */
    public function populateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $clientManager = $this->container->get('fos_oauth_server.client_manager');
        $em->beginTransaction();
        $input         = 'Lorem Ipsum ';
        $person        = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Person')->find($id);
        foreach (range(1, 1) as $val1) {
            $client = new Client();
            $client->setPerson($person);
            $client->setName("Sample client $val1 ".uniqid());
            $client->setDescription('Sample client');
            $client->setSiteUrl("http://localhost");
            $client->setRedirectUris(array('http://localhost'));
            $client->setLandingPageUrl('http://localhost');
            $client->setTermsOfUseUrl('http://localhost');
            $client->setAllowedGrantTypes(Client::getAllGrants());
            $client->setPublished(0);
            $client->setVisible(0);
            $clientManager->updateClient($client);

            $list = array();
            foreach (range(1, 20) as $val2) {
                $cm       = "Sample category $val2 ";
                $category = new Category();
                $category->setClient($client);
                $category->setName($cm.uniqid());
                $category->setDefaultIcon('glyphicon glyphicon-envelope');
                $category->setDefaultTitle($cm." title");
                $category->setDefaultShortText($cm." shorttext");
                $category->setMailTemplate("%title%\r\n%shorttext%\r\n");
                $category->setMailSenderAddress($person->getEmail());
                $category->setEmailable(true);
                $category->setMarkdownTemplate("%title%\r\n--\r\n\r\n> %shorttext%\r\n\r\n");
                $category->setHtmlTemplate(MarkdownExtra::defaultTransform($category->getMarkdownTemplate()));
                $em->persist($category);
                foreach (range(1, 20) as $val3) {
                    $r   = rand(1, 19);
                    $msg = array();
                    if ($r % 2) {
                        $msg['title']     = str_repeat($input, $r);
                        $msg['shorttext'] = str_repeat($input, $r);
                    }
                    $not    = new Notification();
                    $not->setPerson($person);
                    $not->setCategory($category);
                    $not->setIcon($category->getDefaultIcon());
                    $not->setTitle(isset($msg['title']) ? $msg['title'] : $category->getDefaultTitle());
                    $not->setShortText(isset($msg['shorttext']) ? $msg['shorttext']
                                : $category->getDefaultShortText());
                    $not->parseHtmlTemplate($category->getHtmlTemplate());
                    $em->persist($not);
                    $list[] = & $not;
                }
                $list[] = & $category;
            }
            $em->flush();
            $em->clear($client);
            foreach ($list as &$entityes) {
                $em->clear($entityes);
            }
        }
        $em->commit();
        return new Response("ok");
    }
}
