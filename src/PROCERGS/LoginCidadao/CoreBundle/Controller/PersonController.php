<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use LoginCidadao\CoreBundle\Controller\PersonController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Util\TokenGenerator;
use LoginCidadao\CoreBundle\Form\Type\DocFormType;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FormEvent;
use LoginCidadao\CoreBundle\EventListener\ProfileEditListner;
use LoginCidadao\CoreBundle\Form\Type\DocRgFormType;
use LoginCidadao\CoreBundle\Entity\IdCard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;
use LoginCidadao\BadgesBundle\BadgesEvents;
use LoginCidadao\BadgesBundle\Event\EvaluateBadgesEvent;
use LoginCidadao\BadgesBundle\Event\ListBadgesEvent;
use LoginCidadao\CoreBundle\Helper\GridHelper;

class PersonController extends BaseController
{

    /**
     * @Route("/person/authorization/{clientId}/revoke", name="lc_revoke")
     * @Template()
     */
    public function revokeAuthorizationAction(Request $request, $clientId)
    {
        return parent::revokeAuthorizationAction($request, $clientId);
    }

    /**
     * @Route("/person/checkEmailAvailable", name="lc_email_available")
     */
    public function checkEmailAvailableAction(Request $request)
    {
        return parent::checkEmailAvailableAction($request);
    }

    /**
     * @Route("/profile/change-username", name="lc_update_username")
     * @Template()
     */
    public function updateUsernameAction(Request $request)
    {
        return parent::updateUsernameAction($request);
    }

    /**
     * @Route("/cpf/register", name="lc_registration_cpf")
     * @Template("LoginCidadaoCoreBundle:Person:registration/cpf.html.twig")
     */
    public function registrationCpfAction(Request $request)
    {
        return parent::registrationCpfAction($request);
    }

    /**
     * @Route("/facebook/unlink", name="lc_unlink_facebook")
     */
    public function unlinkFacebookAction()
    {
        return parent::unlinkFacebookAction();
    }

    /**
     * @Route("/twitter/unlink", name="lc_unlink_twitter")
     */
    public function unlinkTwitterAction()
    {
        return parent::unlinkTwitterAction();
    }

    /**
     * @Route("/google/unlink", name="lc_unlink_google")
     */
    public function unlinkGoogleAction()
    {
        return parent::unlinkGoogleAction();
    }

    /**
     * @Route("/email/resend-confirmation", name="lc_resend_confirmation_email")
     */
    public function resendConfirmationEmail()
    {
        return parent::resendConfirmationEmail();
    }

    /**
     * @Route("/profile/doc/rg/remove", name="lc_profile_doc_rg_remove")
     * @Template()
     */
    public function docRgRemoveAction(Request $request)
    {
        return parent::docRgRemoveAction($request);
    }

    /**
     * @Route("/profile/doc/rg/edit", name="lc_profile_doc_rg_edit")
     * @Template()
     */
    public function docRgEditAction(Request $request)
    {
        return parent::docRgEditAction($request);
    }

    /**
     * @Route("/profile/doc/rg/list", name="lc_profile_doc_rg_list")
     * @Template()
     */
    public function docRgListAction(Request $request)
    {
        return parent::docRgListAction($request);
    }

    /**
     * @Route("/register/prefilled", name="lc_prefilled_registration")
     */
    public function preFilledRegistrationAction(Request $request)
    {
        return parent::preFilledRegistrationAction($request);
    }

    /**
     * @Route("/profile/badges", name="lc_profile_badges")
     * @Template()
     */
    public function badgesListAction(Request $request)
    {
        return parent::badgesListAction($request);
    }

    /**
     * @Route("/profile/doc/edit", name="lc_profile_doc_edit")
     * @Template()
     */
    public function docEditAction(Request $request)
    {
        $response = parent::docEditAction($request);

        $meuRSHelper = $this->get('meurs.helper');

        $response['personMeuRS'] = $meuRSHelper->getPersonMeuRS($this->getUser(), true);

        return $response;
    }

    public function connectNfgFragmentAction()
    {
        $repo = $this->get('meurs.entities.person_meurs.repository');

        return array(
            'personMeuRS' => $repo->findBy(array(
                'person' => $this->getUser()
            ))
        );
    }
}
