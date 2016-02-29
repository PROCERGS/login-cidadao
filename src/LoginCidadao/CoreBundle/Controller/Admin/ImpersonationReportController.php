<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Controller\Admin;

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
 * @Route("/admin/impersonation/reports")
 * @Security("has_role('FEATURE_IMPERSONATION_REPORTS')")
 */
class ImpersonationReportController extends Controller
{

    /**
     * @Route("/", name="lc_admin_impersonation_report_index")
     * @Template()
     */
    public function indexAction()
    {
        $logRepo    = $this->getDoctrine()
            ->getRepository('LoginCidadaoAPIBundle:ActionLog');
        $reportRepo = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:ImpersonationReport');

        $pending = $logRepo->findImpersonatonsWithoutReports(null,
            $this->getUser(), true);
        $reports = $reportRepo->findBy(array(
            'impersonator' => $this->getUser()
        ));

        return compact('pending', 'reports');
    }

    /**
     * @Route("/new/{logId}", name="lc_admin_impersonation_report_new")
     * @Template()
     */
    public function newAction(Request $request, $logId)
    {
        $log = $this->getActionLogOr404($logId);

        $report = $this->getNewReport($log);
        if ($report instanceof RedirectResponse) {
            return $report;
        }

        $report->setImpersonator($this->getUser());

        $form = $this->createForm('LoginCidadao\CoreBundle\Form\ImpersonationReportType',
            $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($report);
            $em->flush();

            return $this->redirectToRoute('lc_admin_impersonation_report_index');
        }

        return array('form' => $form->createView(), 'report' => $report);
    }

    /**
     * @Route("/{id}/edit", name="lc_admin_impersonation_report_edit", requirements={"id" = "\d+"})
     * @Template()
     * @Security("has_role('ROLE_IMPERSONATION_REPORTS_EDIT')")
     */
    public function editAction(Request $request, $id)
    {
        $report = $this->getOr404($id);

        $form = $this->createForm('LoginCidadao\CoreBundle\Form\ImpersonationReportType',
            $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($report);
            $em->flush();

            return $this->redirectToRoute('lc_admin_impersonation_report_index');
        }

        return array('form' => $form->createView(), 'report' => $report);
    }

    /**
     *
     * @param integer $id
     * @return ActionLog
     * @throws NotFoundHttpException
     */
    private function getActionLogOr404($id)
    {
        $logRepo = $this->getDoctrine()
            ->getRepository('LoginCidadaoAPIBundle:ActionLog');

        $log = $logRepo->find($id);

        if ($log instanceof ActionLog) {
            return $log;
        }

        throw $this->createNotFoundException();
    }

    /**
     *
     * @param integer $id
     * @return ActionLog
     * @throws NotFoundHttpException
     */
    private function getOr404($id)
    {
        $reportRepo = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:ImpersonationReport');

        $report = $reportRepo->find($id);

        if ($report instanceof ImpersonationReport) {
            return $report;
        }

        throw $this->createNotFoundException();
    }

    /**
     *
     * @param ActionLog $log
     * @return ImpersonationReport | RedirectResponse
     * @throws AccessDeniedException
     */
    private function getNewReport(ActionLog $log)
    {
        $reportRepo = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:ImpersonationReport');
        $personRepo = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:Person');

        $report = new ImpersonationReport();

        $existingReport = $reportRepo->findOneBy(array('actionLog' => $log));
        if ($existingReport instanceof ImpersonationReport) {
            $this->addFlash('error', "This action was already reported.");
            return $this->redirectToRoute('lc_admin_impersonation_report_index');
        }

        $impersonatorId = $log->getClientId();

        if ($impersonatorId !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException("You cannot fill other person's report!");
        }

        $targetUser = $personRepo->find($log->getUserId());
        $report->setTarget($targetUser)
            ->setActionLog($log);

        return $report;
    }
}
