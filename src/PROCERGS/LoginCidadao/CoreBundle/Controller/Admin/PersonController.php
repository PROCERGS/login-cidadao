<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\PersonFilterFormType;
use PROCERGS\LoginCidadao\CoreBundle\Helper\GridHelper;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;

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
        $form = $this->createForm(new PersonFilterFormType());
        $form = $form->createView();
        return compact('form');
    }

    /**
     * @Route("/grid", name="lc_admin_person_grid")
     * @Template()
     */
    public function gridAction(Request $request)
    {
        $form = $this->createForm(new PersonFilterFormType());
        $form->handleRequest($request);
        $result['grid'] = null;
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $sql = $em->createQueryBuilder();
            $sql->select('u');
            $sql->from('PROCERGSLoginCidadaoCoreBundle:Person', 'u');
            $sql->where('1=1');
            $parms = $form->getData();
            if (isset($parms['username'][0])) {
                $sql->andWhere('u.cpf like ?1 or LowerUnaccent(u.username) like LowerUnaccent(?1) or LowerUnaccent(u.email) like LowerUnaccent(?1) or LowerUnaccent(u.firstName) like LowerUnaccent(?1) or LowerUnaccent(u.surname) like LowerUnaccent(?1)');
                $sql->setParameter('1',
                                   '%' . addcslashes($parms['username'], '\\%_') . '%');
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
     * @Route("/edit/{id}", name="lc_admin_person_edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Person')->find($id);
        if (!$person) {
            return $this->redirect($this->generateUrl('lc_admin_person'));
        }

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

        $rolesNames = array_keys($roles);

        $form = $this->get('form.factory')->create(
            $this->get('procergs_logincidadao.person.resume.form.type'),
                       $person, array('available_roles' => $rolesNames)
        );
        $form->handleRequest($request);
        if ($form->isValid()) {
            $securityHelper = $this->get('lc.security.helper');
            $loggedUserLevel = $securityHelper->getLoggedInUserLevel();
            $targetPersonLevel = $securityHelper->getTargetPersonLevel($person);

            if ($loggedUserLevel >= $targetPersonLevel) {
                $userManager = $this->get('fos_user.user_manager');
                $userManager->updateUser($person);
                $translator = $this->get('translator');
                $translator->trans('Updated successfully.');
            }
        }

        $user = $this->getUser();
        $defaultClientUid = $this->container->getParameter('oauth_default_client.uid');

        return array(
            'form' => $form->createView(),
            'person' => $person,
            'user' => $user,
            'defaultClientUid' => $defaultClientUid
        );
    }

}
