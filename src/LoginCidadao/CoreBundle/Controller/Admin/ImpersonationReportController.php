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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\CoreBundle\Form\ImpersonationReportType;
use LoginCidadao\CoreBundle\Entity\ImpersonationReport;
use LoginCidadao\APIBundle\Entity\ActionLog;

/**
 * @Route("/admin/impersonation/reports")
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
        $logRepo    = $this->getDoctrine()
            ->getRepository('LoginCidadaoAPIBundle:ActionLog');
        $reportRepo = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:ImpersonationReport');
        $personRepo = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:Person');

        $report = new ImpersonationReport();

        $log = $logRepo->find($logId);
        if ($log instanceof ActionLog) {
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
        }

        $report->setImpersonator($this->getUser());

        $form = $this->createForm(new ImpersonationReportType(), $report);
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
     */
    public function editAction()
    {
        return array();
    }
}
