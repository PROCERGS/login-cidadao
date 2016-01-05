<?php

namespace LoginCidadao\APIBundle\Security\Audit;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use SimpleThings\EntityAudit\AuditConfiguration;
use Doctrine\ORM\EntityManager;
use LoginCidadao\APIBundle\Entity\ActionLog;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use LoginCidadao\APIBundle\Security\Audit\Annotation\Loggable;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class ActionLogger
{

    /** @var SecurityContextInterface */
    protected $security;

    /** @var AuditConfiguration */
    protected $auditConfig;

    /** @var EntityManager */
    protected $em;

    /** @var Request */
    protected $request;

    public function __construct(SecurityContextInterface $security,
                                AuditConfiguration $auditConfig,
                                EntityManager $em)
    {
        $this->security = $security;
        $this->auditConfig = $auditConfig;
        $this->em = $em;
    }

    public function logActivity(Request $request, Loggable $annotation,
                                array $controllerAction)
    {
        $token = $this->security->getToken();
        $user = $token->getUser();
        $auditUsername = $this->auditConfig->getCurrentUsername();

        $log = $this->initLog($request, $annotation->getType(),
                              $controllerAction, $auditUsername);

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
     * @param Loggable $annotation
     * @param Request $request
     * @param array $controllerAction
     * @param string $auditUsername
     * @return ActionLog
     */
    private function initLog(Request $request, $actionType, $controllerAction,
                             $auditUsername)
    {
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
        $accessToken = $accessTokenRepo->findOneBy(array(
            'token' => $token->getToken()
        ));

        $log->setAccessToken($token->getToken());
        $log->setClientId($accessToken->getClient()->getId());
        $log->setUserId($accessToken->getUser()->getId());
    }

    public function registerLogin(Request $request, PersonInterface $person, array $controllerAction)
    {
        $auditUsername = $this->auditConfig->getCurrentUsername();
        $actionType = ActionLog::TYPE_LOGIN;

        $log = $this->initLog($request, $actionType, $controllerAction,
                              $auditUsername);
        $log->setUserId($person->getId());

        $this->em->persist($log);
        $this->em->flush($log);
    }

}
