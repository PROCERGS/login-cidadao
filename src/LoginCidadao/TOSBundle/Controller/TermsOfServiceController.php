<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TOSBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\TOSBundle\Model\TOSInterface;
use LoginCidadao\TOSBundle\Entity\TermsOfService;
use LoginCidadao\TOSBundle\Form\TermsOfServiceType;
use JMS\SecurityExtraBundle\Annotation\Secure;

class TermsOfServiceController extends Controller
{

    /**
     * @Route("/admin/terms", name="tos_admin_list")
     * @Method("GET")
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     */
    public function listAction()
    {
        $termsRepo = $this->getDoctrine()->getRepository('LoginCidadaoTOSBundle:TermsOfService');
        $terms     = $termsRepo->findAll();
        $latest    = $termsRepo->findLatestTerms();

        return compact('terms', 'latest');
    }

    /**
     * @Route("/admin/terms/{id}", name="tos_admin_edit", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @Template()
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function editAction(Request $request, $id)
    {
        $termsRepo = $this->getDoctrine()->getRepository('LoginCidadaoTOSBundle:TermsOfService');
        $terms     = $termsRepo->find($id);
        $form      = $this->getEditForm($terms);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($terms);
            $em->flush();
        }

        return compact('terms', 'form');
    }

    /**
     * @Route("/admin/terms/new", name="tos_admin_new")
     * @Template()
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function newAction()
    {
        $terms = new TermsOfService();
        $form  = $this->getCreateForm($terms);

        return compact('form');
    }

    /**
     * @Route("/terms", name="tos_admin_create")
     * @Method("POST")
     * @Template("LoginCidadaoTOSBundle:TermsOfService:new.html.twig")
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function createAction(Request $request)
    {
        $terms = new TermsOfService();
        $form  = $this->getCreateForm($terms);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $terms->setAuthor($this->getUser());
            $em->persist($terms);
            $em->flush();

            return $this->redirectToRoute('tos_admin_list');
        }

        return compact('form');
    }

    /**
     * @Route("/terms", name="tos_terms")
     * @Template("LoginCidadaoTOSBundle:TermsOfService:latest.html.twig")
     * @Secure(roles="IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function showLatestAction()
    {
        $termsRepo = $this->getDoctrine()->getRepository('LoginCidadaoTOSBundle:TermsOfService');
        $latest    = $termsRepo->findLatestTerms();

        return compact('latest');
    }

    private function getCreateForm(TOSInterface $terms)
    {
        $form = $this->createForm(new TermsOfServiceType(), $terms,
            array(
            'action' => $this->generateUrl('tos_admin_create'),
            'method' => 'POST',
            'translation_domain' => 'LoginCidadaoTOSBundle'
            )
        );
        $form->add('submit', 'submit',
            array(
            'label' => 'tos.form.create.label', 'attr' => array('class' => 'btn-success')
        ));
        return $form;
    }

    private function getEditForm(TOSInterface $terms)
    {
        $form = $this->createForm(new TermsOfServiceType(), $terms,
            array(
            'action' => $this->generateUrl('tos_admin_edit',
                array('id' => $terms->getId())),
            'method' => 'POST',
            'translation_domain' => 'LoginCidadaoTOSBundle'
            )
        );
        $form->add('submit', 'submit',
            array(
            'label' => 'Save', 'attr' => array('class' => 'btn-success')
        ));
        return $form;
    }

    private function redirectToRoute($route, array $parameters = array(),
                                     $status = 302)
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }
}
