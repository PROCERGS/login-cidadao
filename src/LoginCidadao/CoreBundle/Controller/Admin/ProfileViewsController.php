<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Controller\Admin;

use LoginCidadao\APIBundle\Entity\ActionLogRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use LoginCidadao\CoreBundle\Form\ImpersonationReportType;
use LoginCidadao\CoreBundle\Entity\ImpersonationReport;
use LoginCidadao\APIBundle\Entity\ActionLog;

/**
 * @Route("/admin/profile-views/reports")
 * @Security("has_role('ROLE_AUDIT_PROFILE_VIEWS')")
 */
class ProfileViewsController extends Controller
{

    /**
     * @Route("/", name="lc_admin_profile_views_report")
     * @Template()
     */
    public function indexAction()
    {
        /** @var ActionLogRepository $logRepo */
        $logRepo = $this->getDoctrine()->getRepository('LoginCidadaoAPIBundle:ActionLog');
        $data = $logRepo->groupProfileViewsByActor();

        return compact('data');
    }

    /**
     * @Route("/{id}/details", name="lc_admin_profile_views_details")
     * @Template()
     */
    public function detailsAction($id)
    {
        /** @var ActionLogRepository $logRepo */
        $logRepo = $this->getDoctrine()->getRepository('LoginCidadaoAPIBundle:ActionLog');
        $data = $logRepo->listProfileViewsByActor($id);

        return compact('data');
    }
}
