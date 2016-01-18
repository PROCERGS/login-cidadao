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
use LoginCidadao\OAuthBundle\Entity\Organization;
use LoginCidadao\OAuthBundle\Model\OrganizationInterface;

/**
 * @Route("/organizations")
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
     */
    public function newAction(Request $request)
    {
        $organization = new Organization();

        $form = $this->createForm('lc_organization', $organization);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $organization->getMembers()->add($this->getUser());
            $em->persist($organization);
            $em->flush();

            return $this->redirect($this->generateUrl('lc_organizations_list'));
        }

        return compact('form');
    }

    /**
     * @Route("/{id}/edit", name="lc_organizations_edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $organization = $this->getOr404($id);

        $form = $this->createForm('lc_organization', $organization);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $em->persist($organization);
            $em->flush();

            return $this->redirect($this->generateUrl('lc_organizations_list'));
        }

        return compact('form', 'organization');
    }

    private function getOr404($id)
    {
        $organization = $this->getDoctrine()
            ->getRepository('LoginCidadaoOAuthBundle:Organization')
            ->find($id);

        if ($organization instanceof OrganizationInterface) {
            return $organization;
        } else {
            throw new $this->createNotFoundException();
        }
    }

    private function fetchMyOrganizations()
    {
        return $this->getDoctrine()
                ->getRepository('LoginCidadaoOAuthBundle:Organization')
                ->findByMember($this->getUser());
    }

    private function fetchOtherOrganizations()
    {
        return array();
    }
}
