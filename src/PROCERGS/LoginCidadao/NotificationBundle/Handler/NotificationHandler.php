<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Form\NotificationType;
use PROCERGS\LoginCidadao\NotificationBundle\Model\NotificationInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Exception\InvalidFormException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\OAuthBundle\Model\ClientInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Model\CategoryInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\PersonNotificationOption;
use PROCERGS\LoginCidadao\NotificationBundle\Model\NotificationSettings;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class NotificationHandler implements NotificationHandlerInterface
{

    private $om;
    private $entityClass;
    private $repository;
    private $formFactory;
    private $authenticatedHandlers = array();

    public function __construct(ObjectManager $om, $entityClass,
                                FormFactoryInterface $formFactory)
    {
        $this->om = $om;
        $this->entityClass = $entityClass;
        $this->repository = $this->om->getRepository($this->entityClass);
        $this->formFactory = $formFactory;
    }

    public function all($limit = 5, $offset = 0, $orderby = null)
    {
        return $this->repository->findBy(array(), $orderby, $limit, $offset);
    }

    public function getAllFromPerson(PersonInterface $person, $limit = 5,
                                     $offset = 0, $orderby = null)
    {
        return $this->repository->findBy(array('person' => $person), $orderby,
                                         $limit, $offset);
    }

    public function getAllFromPersonByClient(PersonInterface $person,
                                             ClientInterface $client,
                                             $limit = 5, $offset = 0,
                                             $orderby = null)
    {
        return $this->repository->findBy(array('person' => $person, 'sender' => $client),
                                         $orderby, $limit, $offset);
    }

    public function get($id)
    {
        return $this->repository->find($id);
    }

    public function patch(NotificationInterface $notification, array $parameters)
    {
        return $this->processForm($notification, $parameters, 'PATCH');
    }

    public function post(array $parameters)
    {
        $notification = $this->createNotification();

        return $this->processForm($notification, $parameters, 'POST');
    }

    public function put(NotificationInterface $notification, array $parameters)
    {
        return $this->processForm($notification, $parameters, 'PUT');
    }

    /**
     * Processes the form.
     *
     * @param NotificationInterface $notification
     * @param array                 $parameters
     * @param String                $method
     *
     * @return NotificationInterface
     *
     * @throws \PROCERGS\LoginCidadao\CoreBundle\Exception\Notification\InvalidFormException
     */
    private function processForm(NotificationInterface $notification,
                                 array $parameters, $method = "PUT",
                                 PersonInterface $person = null)
    {
        $form = $this->formFactory->create(new NotificationType(),
                                           $notification, compact('method'));
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {

            $notification = $form->getData();

            if (null !== $person && $notification->getPerson()->getId() !== $person->getId()) {
                throw new AccessDeniedHttpException();
            }

            $this->om->persist($notification);
            $this->om->flush($notification);

            return $notification;
        }

        throw new InvalidFormException('Invalid submitted data', $form);
    }

    private function createNotification()
    {
        return new $this->entityClass();
    }

    public function getSettings(PersonInterface $person,
                                CategoryInterface $category = null,
                                ClientInterface $client = null)
    {
        $repo = $this->om->getRepository('PROCERGSLoginCidadaoNotificationBundle:PersonNotificationOption');
        return $repo->findByPerson($person, $category, $client);
    }

    public function getSettingsByClient(PersonInterface $person,
                                        ClientInterface $client)
    {
        $repo = $this->om->getRepository('PROCERGSLoginCidadaoNotificationBundle:PersonNotificationOption');
        return $repo->findByClient($person, $client);
    }

    public function initializeSettings(PersonInterface $person,
                                       ClientInterface $client = null)
    {
        $om = $this->om;
        $categoriesRepo = $om->getRepository('PROCERGSLoginCidadaoNotificationBundle:Category');
        $orphanCategories = $categoriesRepo->findUnconfigured($person, $client);

        if (count($orphanCategories) > 0) {
            foreach ($orphanCategories as $category) {
                $config = new PersonNotificationOption();
                $config->setCategory($category)
                    ->setPerson($person)
                    ->setSendEmail($category->getEmailable())
                    ->setSendPush(true);
                $om->persist($config);
            }
            $om->flush();
        }
    }

    public function markRangeAsRead(PersonInterface $person, $start, $end)
    {
        $om = $this->om;
        $notifications = $om
            ->getRepository('PROCERGSLoginCidadaoNotificationBundle:Notification')
            ->findUntil($person, $start, $end);

        $result = array(
            'read' => array(),
            'failed' => array()
        );
        foreach ($notifications as $notification) {
            try {
                if (!$notification->isRead()) {
                    $notification->setRead(true);
                }
                $result['read'][] = $notification->getId();
            } catch (Exception $e) {
                $result['failed'][] = $notification->getId();
            }
        }
        $om->flush();

        return $result;
    }

    public function getGroupedSettings(PersonInterface $person,
                                       ClientInterface $client = null,
                                       CategoryInterface $category = null)
    {
        $settings = new NotificationSettings();

        $options = $this->getSettings($person, $category, $client);
        foreach ($options as $option) {
            $settings->addOption($option);
        }

        return $settings;
    }

    public function getAuthenticatedHandler(PersonInterface $person)
    {
        $id = $person->getId();
        if (!array_key_exists($id, $this->authenticatedHandlers)) {
            $this->authenticatedHandlers[$id] = new AuthenticatedNotificationHandler($person,
                                                                                     $this);
        }
        return $this->authenticatedHandlers[$id];
    }

}
