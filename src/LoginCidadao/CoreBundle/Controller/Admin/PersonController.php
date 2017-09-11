<?php

namespace LoginCidadao\CoreBundle\Controller\Admin;

use LoginCidadao\APIBundle\Security\Audit\ActionLogger;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Helper\GridHelper;
use LoginCidadao\CoreBundle\Model\PersonInterface;

/**
 * @Route("/admin/person")
 * @Security("has_role('ROLE_PERSON_EDIT')")
 */
class PersonController extends Controller
{

    /**
     * @Route("/", name="lc_admin_person")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm('LoginCidadao\CoreBundle\Form\Type\PersonFilterFormType');
        $form = $form->createView();

        return compact('form');
    }

    /**
     * @Route("/grid", name="lc_admin_person_grid")
     * @Template()
     */
    public function gridAction(Request $request)
    {
        $form = $this->createForm('LoginCidadao\CoreBundle\Form\Type\PersonFilterFormType');
        $form->handleRequest($request);
        $gridView = null;
        if ($form->isValid()) {
            $data = $form->getData();

            $grid = new GridHelper();
            $grid->setId('person-grid');
            $grid->setPerPage(5);
            $grid->setMaxResult(5);
            $grid->setInfiniteGrid(true);
            $grid->setRoute('lc_admin_person_grid');
            $grid->setRouteParams([$form->getName()]);

            if ($data['username']) {
                /** @var PersonRepository $repo */
                $repo = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:Person');
                $query = $repo->getSmartSearchQuery($data['username']);
                $grid->setQueryBuilder($query);
            }

            $gridView = $grid->createView($request);
        }

        return ['grid' => $gridView];
    }

    /**
     * @Route("/{id}/edit", name="lc_admin_person_edit", requirements={"id" = "\d+"})
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        /** @var PersonInterface $person */
        $person = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:Person')->find($id);
        if (!$person) {
            return $this->redirectToRoute('lc_admin_person');
        }

        /** @var ActionLogger $actionLogger */
        $actionLogger = $this->get('lc.action_logger');
        $actionLogger->registerProfileView($request, $person, $this->getUser(), [$this, 'editAction']);

        $form = $this->createPersonForm($person);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $securityHelper = $this->get('lc.security.helper');
            $loggedUserLevel = $securityHelper->getLoggedInUserLevel();
            $targetPersonLevel = $securityHelper->getTargetPersonLevel($person);

            if ($loggedUserLevel >= $targetPersonLevel) {
                $this->get('fos_user.user_manager')->updateUser($person);
                $this->addFlash('success', $this->get('translator')->trans('Updated successfully.'));
            }
        }

        $defaultClientUid = $this->container->getParameter('oauth_default_client.uid');

        return ['form' => $form->createView(), 'person' => $person, 'defaultClientUid' => $defaultClientUid];
    }

    private function getRolesNames()
    {
        $rolesHierarchy = $this->container->getParameter('security.role_hierarchy.roles');
        $roles = array();

        foreach ($rolesHierarchy as $role => $children) {
            $roles[$role] = $children;
            foreach ($children as $child) {
                if (!array_key_exists($child, $roles)) {
                    $roles[$child] = 0;
                }
            }
        }

        return array_keys($roles);
    }

    /**
     * @param PersonInterface $person
     * @return FormInterface
     */
    private function createPersonForm(PersonInterface $person)
    {
        $rolesNames = $this->getRolesNames();

        return $this->get('form.factory')->create(
            $this->get('lc.person.resume.form.type'),
            $person,
            array('available_roles' => $rolesNames)
        );
    }

    /**
     * @Route("/{id}/reports", name="lc_admin_person_impersonation_reports", requirements={"id" = "\d+"})
     * @Template()
     */
    public function impersonationReportsAction($id)
    {
        $reports = array();
        $person = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:Person')->find($id);

        if ($person instanceof PersonInterface) {
            $reportRepo = $this->getDoctrine()
                ->getRepository('LoginCidadaoCoreBundle:ImpersonationReport');

            $criteria = array('target' => $person);
            if (false === $this->isGranted('ROLE_IMPERSONATION_REPORTS_LIST_ALL')) {
                $criteria['impersonator'] = $this->getUser();
            }

            $reports = $reportRepo->findBy($criteria);
        }

        return compact('reports');
    }
}
