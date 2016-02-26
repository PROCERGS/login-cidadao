<?php

namespace LoginCidadao\CoreBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Helper\GridHelper;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Form\Type\PersonFilterFormType;

/**
 * @Route("/admin/person")
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
        $form           = $this->createForm('LoginCidadao\CoreBundle\Form\Type\PersonFilterFormType');
        $form->handleRequest($request);
        $result['grid'] = null;
        if ($form->isValid()) {
            $em    = $this->getDoctrine()->getManager();
            $sql   = $em->createQueryBuilder();
            $sql->select('u');
            $sql->from('LoginCidadaoCoreBundle:Person', 'u');
            $sql->where('1=1');
            $parms = $form->getData();
            if (isset($parms['username'][0])) {
                $sql->andWhere('u.cpf like ?1 or LowerUnaccent(u.username) like LowerUnaccent(?1) or LowerUnaccent(u.email) like LowerUnaccent(?1) or LowerUnaccent(u.firstName) like LowerUnaccent(?1) or LowerUnaccent(u.surname) like LowerUnaccent(?1)');
                $sql->setParameter('1',
                    '%'.addcslashes($parms['username'], '\\%_').'%');
            }
            $sql->addOrderBy('u.id', 'desc');

            $grid = new GridHelper();
            $grid->setId('person-grid');
            $grid->setPerPage(5);
            $grid->setMaxResult(5);
            $grid->setQueryBuilder($sql);
            $grid->setInfiniteGrid(true);
            $grid->setRoute('lc_admin_person_grid');
            $grid->setRouteParams(array(
                $form->getName()
            ));
            return array(
                'grid' => $grid->createView($request)
            );
        }
        return $result;
    }

    /**
     * @Route("/{id}/edit", name="lc_admin_person_edit", requirements={"id" = "\d+"})
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $person = $this->getDoctrine()
                ->getRepository('LoginCidadaoCoreBundle:Person')->find($id);
        if (!$person) {
            return $this->redirect($this->generateUrl('lc_admin_person'));
        }

        $form = $this->createPersonForm($person);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $securityHelper    = $this->get('lc.security.helper');
            $loggedUserLevel   = $securityHelper->getLoggedInUserLevel();
            $targetPersonLevel = $securityHelper->getTargetPersonLevel($person);

            if ($loggedUserLevel >= $targetPersonLevel) {
                $userManager = $this->get('fos_user.user_manager');
                $userManager->updateUser($person);
                $translator  = $this->get('translator');
                $translator->trans('Updated successfully.');
            }
        }

        $user             = $this->getUser();
        $defaultClientUid = $this->container->getParameter('oauth_default_client.uid');

        return array(
            'form' => $form->createView(),
            'person' => $person,
            'user' => $user,
            'defaultClientUid' => $defaultClientUid
        );
    }

    private function getRolesNames()
    {
        $rolesHierarchy = $this->container->getParameter('security.role_hierarchy.roles');
        $roles          = array();

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

    private function createPersonForm(PersonInterface $person)
    {
        $rolesNames = $this->getRolesNames();

        return $this->get('form.factory')->create(
                $this->get('lc.person.resume.form.type'), $person,
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
        $person  = $this->getDoctrine()
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
