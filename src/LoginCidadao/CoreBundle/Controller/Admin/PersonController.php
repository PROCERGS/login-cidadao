<?php

namespace LoginCidadao\CoreBundle\Controller\Admin;

use Doctrine\ORM\NonUniqueResultException;
use libphonenumber\PhoneNumber;
use LoginCidadao\APIBundle\Security\Audit\ActionLogger;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Form\Type\PersonFilterFormType;
use LoginCidadao\CoreBundle\Form\Type\PersonResumeFormType;
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;
use LoginCidadao\TOSBundle\Model\TOSManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Helper\GridHelper;
use LoginCidadao\CoreBundle\Model\PersonInterface;

/**
 * @Route("/admin/person")
 * @Security("has_role('ROLE_PERSON_EDIT')")
 * @codeCoverageIgnore
 */
class PersonController extends Controller
{

    /**
     * @Route("/", name="lc_admin_person")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $data = null;
        if ($request->get('search') !== null) {
            $data = ['username' => $request->get('search')];
        }
        $form = $this->createForm(PersonFilterFormType::class, $data);
        $form = $form->createView();

        return compact('form');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/search", name="lc_admin_person_search")
     */
    public function smartSearchAction(Request $request)
    {
        $searchQuery = $request->get('query');

        /** @var PersonRepository $repo */
        $repo = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:Person');
        try {
            $person = $repo->getSmartSearchQuery($searchQuery)
                ->getQuery()->getOneOrNullResult();

            if ($person instanceof PersonInterface) {
                return $this->redirectToRoute('lc_admin_person_edit', ['id' => $person->getId()]);
            }
        } catch (NonUniqueResultException $e) {
            // Failed...
        }

        return $this->redirectToRoute('lc_admin_person', ['search' => $searchQuery]);
    }

    /**
     * @param Request $request
     * @param $id
     * @param $token
     * @return Response
     *
     * @Route("/{id}/block/{token}", name="lc_admin_person_block")
     * @Security("has_role('ROLE_PERSON_BLOCK')")
     */
    public function blockAction(Request $request, $id, $token)
    {
        if (!$this->isBlockTokenValid($request->getSession(), $id, $token)) {
            $this->addFlash('error', $this->get('translator')->trans('lc.admin.person.block.invalid_token'));

            return $this->redirectToRoute('lc_admin_person_edit', ['id' => $id]);
        }

        /** @var UserManager $userManager */
        $userManager = $this->get('lc.user_manager');
        /** @var PersonRepository $repo */
        $repo = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:Person');

        $person = $repo->find($id);
        if (!$person instanceof PersonInterface) {
            return $this->redirectToRoute('lc_admin_person');
        }

        $blockResponse = $userManager->blockPerson($person);
        if (null === $blockResponse) {
            $this->addFlash('error', $this->get('translator')->trans('lc.admin.person.block.failed'));
        } else {
            $this->addFlash('success', $this->get('translator')->trans('lc.admin.person.block.success'));
        }

        return $this->redirectToRoute('lc_admin_person_edit', ['id' => $id]);
    }

    /**
     * @Route("/grid", name="lc_admin_person_grid")
     * @Template()
     */
    public function gridAction(Request $request)
    {
        $form = $this->createForm(PersonFilterFormType::class);
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
        /** @var PersonRepository $repo */
        $repo = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:Person');

        /** @var PersonInterface $person */
        $person = $repo->find($id);
        if (!$person) {
            return $this->redirectToRoute('lc_admin_person');
        }

        /** @var ActionLogger $actionLogger */
        $actionLogger = $this->get('lc.action_logger');
        $actionLogger->registerProfileView($request, $person, $this->getUser(), [$this, 'editAction']);

        /** @var TOSManager $tosManager */
        $tosManager = $this->get('tos.manager');
        $agreement = $tosManager->getCurrentTermsAgreement($person);

        $phone = $person->getMobile();
        $phoneVerification = null;
        $samePhoneCount = 0;
        if ($phone instanceof PhoneNumber) {
            $samePhoneCount = $repo->countByPhone($phone);

            /** @var PhoneVerificationServiceInterface $phoneVerificationService */
            $phoneVerificationService = $this->get('phone_verification');
            $phoneVerification = $phoneVerificationService->getPhoneVerification($person, $person->getMobile());
        }

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

        $blockToken = $this->setBlockToken($request->getSession(), $person->getId());

        return [
            'form' => $form->createView(),
            'person' => $person,
            'phoneVerification' => $phoneVerification,
            'samePhoneCount' => $samePhoneCount,
            'defaultClientUid' => $defaultClientUid,
            'agreement' => $agreement,
            'blockToken' => $blockToken,
        ];
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
            PersonResumeFormType::class,
            $person,
            ['available_roles' => $rolesNames]
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

    private function setBlockToken(SessionInterface $session, $id)
    {
        $token = bin2hex(random_bytes(64));
        $session->set("block_token_{$id}", $token);

        return $token;
    }

    /**
     * @param SessionInterface $session
     * @param mixed $id
     * @param string $token
     * @return bool
     */
    private function isBlockTokenValid(SessionInterface $session, $id, $token, $clear = true)
    {
        $key = "block_token_{$id}";
        $stored = $session->get($key);
        if ($clear) {
            $session->remove($key);
        }

        return $stored !== null && $stored === $token;
    }
}
