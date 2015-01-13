<?php

namespace PROCERGS\LoginCidadao\APIBundle\Security\Audit;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use SimpleThings\EntityAudit\AuditConfiguration;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\APIBundle\Entity\ActionLog;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use PROCERGS\LoginCidadao\APIBundle\Security\Audit\Annotation\Loggable;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;

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
        $controller = get_class($controllerAction[0]);
        $action = $controllerAction[1];

        $log = new ActionLog();
        $log->setController($controller);
        $log->setAction($action);
        $log->setActionType($annotation->getType());
        $log->setMethod($request->getMethod());
        $log->setUri($request->getUri());
        $log->setAuditUsername($auditUsername);
        $log->setIp($request->getClientIp());

        if ($user instanceof PersonInterface) {
            $log->setPerson($user);
        }

        if ($token instanceof OAuthToken) {
            $log->setAccessToken($token->getToken());
        }

        //var_dump($request->attributes); die();

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
        $repo = $this->em->getRepository('PROCERGSLoginCidadaoAPIBundle:ActionLog');
        $log = $repo->find($id);
        if ($log instanceof ActionLog) {
            return $log;
        } else {
            return null;
        }
    }

}
