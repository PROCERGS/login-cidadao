<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use LoginCidadao\OAuthBundle\Entity\Organization;
use LoginCidadao\OAuthBundle\Model\OrganizationInterface;

/**
 * @Route("/organizations")
 * @Security("has_role('FEATURE_ORGANIZATIONS')")
 */
class OrganizationController extends Controller
{

    /**
     * @Route("/", name="lc_organizations_list")
     * @Template()
     */
    public function listAction()
    {
        $checker = $this->get('security.authorization_checker');

        $myOrganizations    = $this->fetchMyOrganizations();
        $otherOrganizations = array();

        if ($checker->isGranted('ROLE_ORGANIZATIONS_LIST_ALL')) {
            $otherOrganizations = $this->fetchOtherOrganizations();
        }

        return compact('myOrganizations', 'otherOrganizations');
    }

    /**
     * @Route("/new", name="lc_organizations_new")
     * @Template()
     * @Security("has_role('ROLE_ORGANIZATIONS_CREATE')")
     */
    public function newAction(Request $request)
    {
        $organization = new Organization();

        $form = $this->createForm('LoginCidadao\OAuthBundle\Form\OrganizationType',
            $organization);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $organization->getMembers()->add($this->getUser());
            $em->persist($organization);
            $em->flush();

            return $this->redirectToRoute('lc_organizations_list');
        }

        return compact('form');
    }

    /**
     * @Route("/{id}/edit", name="lc_organizations_edit", requirements={"id" = "\d+"})
     * @Template()
     * @Security("has_role('ROLE_ORGANIZATIONS_EDIT')")
     */
    public function editAction(Request $request, $id)
    {
        $organization = $this->getOr404($id);

        if (!$organization->getMembers()->contains($this->getUser())) {
            $this->denyAccessUnlessGranted('ROLE_ORGANIZATIONS_EDIT_ANY_ORG');
        }

        $form = $this->createForm('LoginCidadao\OAuthBundle\Form\OrganizationType',
            $organization);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $organization->getValidationSecret();
            $em->persist($organization);
            $em->flush();

            return $this->redirectToRoute('lc_organizations_list');
        }

        return compact('form', 'organization');
    }

    /**
     * @Route("/{id}", name="lc_organizations_show", requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
        $organization = $this->getOr404($id);

        return compact('organization');
    }

    /**
     * @Route("/{id}/delete", name="lc_organizations_delete", requirements={"id" = "\d+"})
     * @Template()
     */
    public function deleteAction(Request $request, $id)
    {
        $organization = $this->getOr404($id);

        $em   = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder($organization)
            ->add('delete', 'submit',
                array(
                'label' => 'organizations.form.delete.yes',
                'attr' => array('class' => 'btn-danger')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->remove($organization);
            $em->flush();

            $translator = $this->get('translator');
            $params     = array('%name%' => $organization->getName());
            $message    = $translator->trans('organizations.form.delete.success',
                $params);
            $this->get('session')->getFlashBag()->add('success', $message);

            return $this->redirectToRoute('lc_organizations_list');
        }

        return compact('organization', 'form');
    }

    private function getOr404($id)
    {
        $organization = $this->getDoctrine()
            ->getRepository('LoginCidadaoOAuthBundle:Organization')
            ->find($id);

        if ($organization instanceof OrganizationInterface) {
            return $organization;
        } else {
            throw $this->createNotFoundException();
        }
    }

    private function fetchMyOrganizations()
    {
        return $this->getDoctrine()
                ->getRepository('LoginCidadaoOAuthBundle:Organization')
                ->findByMember($this->getUser());
    }

    /**
     * @Security("has_role('ROLE_ORGANIZATIONS_LIST_ALL')")
     */
    private function fetchOtherOrganizations()
    {
        return $this->getDoctrine()
                ->getRepository('LoginCidadaoOAuthBundle:Organization')
                ->findByNotMember($this->getUser());
    }
}
