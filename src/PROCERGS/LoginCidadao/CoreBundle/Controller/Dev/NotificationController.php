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
use PROCERGS\LoginCidadao\CoreBundle\Entity\ConfigNotCli;

/**
 * @Route("/dev/not")
 */
class NotificationController extends Controller
{

    /**
     * @Route("/new", name="lc_dev_not_new")
     * @Template()
     */
    public function newAction()
    {
        $client = new ConfigNotCli();
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.client.not.cat.form.type'), $client);
        
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($client);
            $manager->flush();
            return $this->redirect($this->generateUrl('lc_dev_not_edit', array(
                'id' => $client->getId()
            )));
        }
        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/list", name="lc_dev_not_list")
     * @Template()
     */
    public function listAction()
    {
        return array();
    }

    /**
     * @Route("/grid", name="lc_dev_not_grid")
     * @Template()
     */
    public function gridAction()
    {
        $clients = $this->getDoctrine()
            ->getManager()
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli')
            ->createQueryBuilder('u')
            ->join('PROCERGSOAuthBundle:Client', 'c', 'with', 'u.client = c')
            ->where('c.person = :person')
            ->setParameter('person', $this->getUser())
            ->orderBy('u.id', 'desc')
            ->getQuery()
            ->getResult();
        return array(
            'resultset' => $clients
        );
    }

    /**
     * @Route("/edit/{id}", name="lc_dev_not_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli')
        ->createQueryBuilder('u')
        ->join('PROCERGSOAuthBundle:Client', 'c', 'with', 'u.client = c')
        ->where('c.person = :person and u.id = :id')
        ->setParameter('person', $this->getUser())
        ->setParameter('id', $id)
        ->orderBy('u.id', 'desc')
        ->getQuery()
        ->getSingleResult();
        if (!$client) {
            return $this->redirect($this->generateUrl('lc_dev_not_edit'));
        }
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.client.not.cat.form.type'), $client);
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($client);
            $manager->flush();
        }
        return $this->render('PROCERGSLoginCidadaoCoreBundle:Dev\Notification:new.html.twig', array(
            'form' => $form->createView(),
            'client' => $client
        ));
    }
}
