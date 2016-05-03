<?php

namespace LoginCidadao\OAuthBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\CoreBundle\Helper\GridHelper;

class OrganizationMembersController extends Controller
{

    /**
     * @Route("/organizations/members/filter", name="lc_organizations_members_filter")
     * @Template()
     */
    public function memberSearchAction(Request $request)
    {
        $grid = new GridHelper();
        $grid->setId('developer-filter-grid')
            ->setPerPage(5)
            ->setMaxResult(5)
            ->setInfiniteGrid(true)
            ->setRouteParams(array('ac_data'))
            ->setRoute('lc_organizations_members_filter');

        $parms = $request->get('ac_data');
        if (isset($parms['username'])) {
            $query = $this->getDoctrine()
                ->getRepository('LoginCidadaoCoreBundle:Person')
                ->getUserSearchQuery($parms['username']);

            $grid->setQueryBuilder($query);
        }

        return array('grid' => $grid->createView($request));
    }

    /**
     * @Route("/organizations/members", name="lc_organizations_members")
     * @Template()
     */
    public function listMembersAction(Request $request)
    {
        $grid = new GridHelper();
        $grid->setId('developer-grid')
            ->setPerPage(5)
            ->setMaxResult(5)
            ->setInfiniteGrid(true)
            ->setRouteParams(array('ac_data'))
            ->setRoute('lc_organizations_members');

        $parms = $request->get('ac_data');
        if (isset($parms['person_id']) && !empty($parms['person_id'])) {
            $query = $this->getDoctrine()
                ->getRepository('LoginCidadaoCoreBundle:Person')
                ->getFindByIdIn($parms['person_id']);
            $grid->setQueryBuilder($query);
        }
        return array('grid' => $grid->createView($request));
    }
}
