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

use LoginCidadao\OAuthBundle\Entity\OrganizationRepository;
use LoginCidadao\OAuthBundle\Form\OrganizationType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

        $myOrganizations = $this->fetchMyOrganizations();
        $otherOrganizations = [];

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

        $form = $this->createForm(OrganizationType::class, $organization);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $organization->getMembers()[] = $this->getUser();
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

        $form = $this->createForm(OrganizationType::class, $organization);
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
    public function showAction($id)
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

        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder($organization)
            ->add('delete', SubmitType::class, [
                'label' => 'organizations.form.delete.yes',
                'attr' => ['class' => 'btn-danger'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->remove($organization);
            $em->flush();

            $translator = $this->get('translator');
            $params = ['%name%' => $organization->getName()];
            $message = $translator->trans('organizations.form.delete.success', $params);
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
        return $this->getOrganizationRepository()->findByMember($this->getUser());
    }

    /**
     * @Security("has_role('ROLE_ORGANIZATIONS_LIST_ALL')")
     */
    private function fetchOtherOrganizations()
    {
        return $this->getOrganizationRepository()->findByNotMember($this->getUser());
    }

    private function getOrganizationRepository()
    {
        /** @var OrganizationRepository $repo */
        $repo = $this->getDoctrine()->getRepository('LoginCidadaoOAuthBundle:Organization');

        return $repo;
    }
}
