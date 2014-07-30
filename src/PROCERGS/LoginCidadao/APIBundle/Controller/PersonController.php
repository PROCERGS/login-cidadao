<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use PROCERGS\LoginCidadao\APIBundle\Exception\RequestTimeoutException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;

class PersonController extends FOSRestController
{

    /**
     * @REST\Get("/person")
     * @REST\View
     */
    public function selfAction()
    {
        $person = $this->getUser();
        $scope = $this->getClientScope($person);

        $view = $this->view($this->preparePerson($person))
                ->setSerializationContext($this->getSerializationContext($scope));
        return $this->handleView($view);
    }

    /**
     * @REST\Get("/wait/person/update")
     * @REST\View
     */
    public function waitPersonChangeAction()
    {
        $user = $this->getUser();
        $scope = $this->getClientScope($user);
        $updatedAt = \DateTime::createFromFormat('Y-m-d H:i:s',
                        $this->getRequest()->get('updated_at'));

        if (!($updatedAt instanceof \DateTime)) {
            $updatedAt = new \DateTime();
        }

        $id = $user->getId();
        $lastUpdatedAt = null;
        $callback = $this->getCheckUpdateCallback($id, $updatedAt,
                $lastUpdatedAt);
        $person = $this->runTimeLimited($callback);
        $context = SerializationContext::create()->setGroups($scope);
        $view = $this->view($this->preparePerson($person))
                ->setSerializationContext($context);
        return $this->handleView($view);
    }

    private function runTimeLimited($callback, $waitTime = 1)
    {
        $maxExecutionTime = ini_get('max_execution_time');
        $limit = $maxExecutionTime ? $maxExecutionTime - 2 : 60;
        $startTime = time();
        while ($limit > 0) {
            $result = call_user_func($callback);
            $delta = time() - $startTime;

            if ($result !== false) {
                return $result;
            }

            $limit -= $delta;
            if ($limit <= 0) {
                break;
            }
            $startTime = time();
            sleep($waitTime);
        }
        throw new RequestTimeoutException("Request Timeout");
    }

    private function preparePerson(Person $person)
    {
        $imgHelper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $templateHelper = $this->get('templating.helper.assets');
        $isDev = $this->get('kernel')->getEnvironment() === 'dev';
        $person->prepareAPISerialize($imgHelper, $templateHelper, $isDev,
                $this->getRequest());

        return $person;
    }

    private function serializePerson($person, $scope)
    {
        $person = $this->preparePerson($person, $scope);

        $serializer = $this->container->get('jms_serializer');
        return $serializer->serialize($person, 'json',
                        SerializationContext::create()->setGroups($scope));
    }

    private function getClientScope($user)
    {
        $token = $this->get('security.context')->getToken();
        $accessToken = $this->getDoctrine()->getRepository('PROCERGSOAuthBundle:AccessToken')->findOneBy(array('token' => $token->getToken()));
        $client = $accessToken->getClient();

        $authorization = $this->getDoctrine()
                ->getRepository('PROCERGSLoginCidadaoCoreBundle:Authorization')
                ->findOneBy(array(
            'person' => $user,
            'client' => $client
        ));
        if (!($authorization instanceof Authorization)) {
            throw new AccessDeniedException();
        }

        return $authorization->getScope();
    }

    private function getCheckUpdateCallback($id, $updatedAt, $lastUpdatedAt)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $people = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Person');
        return function() use ($id, $people, $em, $updatedAt, $lastUpdatedAt) {
            $em->clear();
            $person = $people->find($id);
            if (!$person->getUpdatedAt()) {
                return false;
            }

            if ($person->getUpdatedAt() > $updatedAt) {
                return $person;
            }

            if ($lastUpdatedAt === null) {
                $lastUpdatedAt = $person->getUpdatedAt();
            } elseif ($person->getUpdatedAt() != $lastUpdatedAt) {
                return $person;
            }

            return false;
        };
    }

    private function getSerializationContext($scope)
    {
        return SerializationContext::create()->setGroups($scope);
    }
    
    /**
     * @REST\Post("/person/sendnotification")
     * @REST\View
     */
    public function sendNotificationAction(Request $request)
    {
        if (!$request->get('id_config') || !$request->get('text')) {
            throw new  HttpException(400, "missing required fields");
        }
        $person = $this->getUser();
        $token = $this->get('security.context')->getToken();
        $accessToken = $this->getDoctrine()->getRepository('PROCERGSOAuthBundle:AccessToken')->findOneBy(array('token' => $token->getToken()));
        $client = $accessToken->getClient();
        
        $authorization = $this->getDoctrine()
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:Authorization')
        ->findOneBy(array(
            'person' => $person,
            'client' => $client
        ));
        if (!($authorization instanceof Authorization)) {
            throw new AccessDeniedException();
        }
        $configNotCli = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli')->findOneBy(array('client' => $client, 'id' => $request->get('id_config')));
        if (!$configNotCli) {
            throw new  HttpException(400, "category not found");
        }
        $notification = new Notification();
        $notification->setPerson($person);
        $notification->setConfigNotCli($configNotCli)
        ->setIcon($request->get('icon') ? $request->get('icon') : $configNotCli->getIcon())
        ->setTitle($request->get('title') ? $request->get('title') : $configNotCli->getTitle())
        ->setShortText($request->get('shorttext') ? $request->get('shorttext') : $configNotCli->getShortText())
        ->setText($request->get('text'));
        $this->getDoctrine()->getManager()->persist($notification);
        $this->getDoctrine()->getManager()->flush();
        return $this->handleView($this->view(array('id' => $notification->getId())));
    }

}
