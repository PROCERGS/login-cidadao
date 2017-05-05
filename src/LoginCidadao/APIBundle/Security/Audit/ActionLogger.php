<?php

namespace LoginCidadao\APIBundle\Security\Audit;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use SimpleThings\EntityAudit\AuditConfiguration;
use Doctrine\ORM\EntityManager;
use LoginCidadao\APIBundle\Entity\ActionLog;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use LoginCidadao\APIBundle\Security\Audit\Annotation\Loggable;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class ActionLogger
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuditConfiguration */
    protected $auditConfig;

    /** @var EntityManager */
    protected $em;

    /** @var Request */
    protected $request;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuditConfiguration $auditConfig,
        EntityManager $em
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->auditConfig = $auditConfig;
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @param Loggable $annotation
     * @param array $controllerAction
     */
    public function logActivity(
        Request $request,
        Loggable $annotation,
        array $controllerAction
    ) {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        $auditUsername = $this->auditConfig->getCurrentUsername();

        $log = $this->initLog(
            $request,
            $annotation->getType(),
            $controllerAction,
            $auditUsername
        );

        if ($user instanceof PersonInterface) {
            $log->setUserId($user->getId());
        }

        $this->logAccessToken($log, $token);

        $this->em->persist($log);
        $this->em->flush($log);

        $annotation->setActionLogId($log->getId());
    }

    public function updateResponseCode($actionLogId, $responseCode)
    {

        $log = $this->getActionLog($actionLogId);
        $log->setResponseCode($responseCode);

        $this->em->flush($log);
    }

    /**
     * @param integer $id
     * @return ActionLog | null
     */
    private function getActionLog($id)
    {
        $repo = $this->em->getRepository('LoginCidadaoAPIBundle:ActionLog');
        $log = $repo->find($id);

        if ($log instanceof ActionLog) {
            return $log;
        } else {
            return null;
        }
    }

    /**
     * @param Request $request
     * @param $actionType
     * @param array $controllerAction
     * @param string $auditUsername
     * @return ActionLog
     * @internal param Loggable $annotation
     */
    private function initLog(
        Request $request,
        $actionType,
        $controllerAction,
        $auditUsername
    ) {
        $controller = get_class($controllerAction[0]);
        $action = $controllerAction[1];

        $log = new ActionLog();
        $log->setController($controller);
        $log->setAction($action);
        $log->setActionType($actionType);
        $log->setMethod($request->getMethod());
        $log->setUri($request->getUri());
        $log->setAuditUsername($auditUsername);
        $log->setIp($request->getClientIp());

        return $log;
    }

    private function logAccessToken(ActionLog $log, TokenInterface $token)
    {
        if (!($token instanceof OAuthToken)) {
            return;
        }

        $accessTokenRepo = $this->em->getRepository('LoginCidadaoOAuthBundle:AccessToken');
        $accessToken = $accessTokenRepo->findOneBy(
            array(
                'token' => $token->getToken(),
            )
        );

        $log->setAccessToken($token->getToken());
        $log->setClientId($accessToken->getClient()->getId());
        $log->setUserId($accessToken->getUser()->getId());
    }

    public function registerLogin(
        Request $request,
        PersonInterface $person,
        array $controllerAction
    ) {
        $auditUsername = $this->auditConfig->getCurrentUsername();
        $actionType = ActionLog::TYPE_LOGIN;

        $log = $this->initLog(
            $request,
            $actionType,
            $controllerAction,
            $auditUsername
        );
        $log->setUserId($person->getId());

        $this->em->persist($log);
        $this->em->flush($log);
    }

    public function registerImpersonate(
        Request $request,
        PersonInterface $person,
        PersonInterface $impersonator,
        array $controllerAction,
        $isImpersonating
    ) {
        $auditUsername = $this->auditConfig->getCurrentUsername();
        if ($isImpersonating) {
            $actionType = ActionLog::TYPE_IMPERSONATE;
        } else {
            $actionType = ActionLog::TYPE_DEIMPERSONATE;
        }

        $this->registerActionLog($request, $person, $impersonator, $controllerAction, $auditUsername, $actionType);
    }

    /**
     * @param Request $request
     * @param PersonInterface $person
     * @param PersonInterface $viewer
     * @param array $controllerAction
     */
    public function registerProfileView(
        Request $request,
        PersonInterface $person,
        PersonInterface $viewer,
        array $controllerAction
    ) {
        $auditUsername = $this->auditConfig->getCurrentUsername();
        $actionType = ActionLog::TYPE_PROFILE_VIEW;

        $this->registerActionLog($request, $person, $viewer, $controllerAction, $auditUsername, $actionType);
    }

    private function registerActionLog(
        Request $request,
        PersonInterface $person,
        PersonInterface $actor,
        array $controllerAction,
        $auditUsername,
        $actionType
    ) {
        $log = $this->initLog(
            $request,
            $actionType,
            $controllerAction,
            $auditUsername
        );
        $log->setUserId($person->getId());
        $log->setClientId($actor->getId());

        $this->em->persist($log);
        $this->em->flush($log);
    }
}
