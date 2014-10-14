<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Form\NotificationType;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\NotificationInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Exception\InvalidFormException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\OAuthBundle\Model\ClientInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Model\CategoryInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\PersonNotificationOption;

class NotificationHandler implements NotificationHandlerInterface
{

    private $om;
    private $entityClass;
    private $repository;
    private $formFactory;

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

    public function getAllFromPerson(Person $person, $limit = 5, $offset = 0,
                                     $orderby = null)
    {
        return $this->repository->findBy(array('person' => $person), $orderby,
                                         $limit, $offset);
    }

    public function getAllFromPersonByClient(Person $person,
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
                                 array $parameters, $method = "PUT")
    {
        $form = $this->formFactory->create(new NotificationType(),
                                           $notification, compact('method'));
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {

            $notification = $form->getData();
            $this->om->persist($notification);
            $this->om->flush($notification);

            return $notification;
        }
        // Debuging stuff
        //*
        foreach ($form->all() as $f) {
            $errors = $f->getErrors();
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $form->addError($error);
                }
                echo $f->getName();
                print_r($errors);
            }
        }
        // */

        throw new InvalidFormException('Invalid submitted data', $form);
    }

    private function createNotification()
    {
        return new $this->entityClass();
    }

    public function getSettings(Person $person,
                                CategoryInterface $category = null)
    {
        $repo = $this->om->getRepository('PROCERGSLoginCidadaoNotificationBundle:PersonNotificationOption');
        $filter = compact('person');
        if (null !== $category) {
            $filter['category'] = $category;
        }

        return $repo->findBy($filter);
    }

    public function getSettingsByClient(Person $person, ClientInterface $client)
    {
        $repo = $this->om->getRepository('PROCERGSLoginCidadaoNotificationBundle:PersonNotificationOption');
        $repo->findByClient($person, $client);
    }

    public function initializeSettings(Person $person, ClientInterface $client = null)
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

}
