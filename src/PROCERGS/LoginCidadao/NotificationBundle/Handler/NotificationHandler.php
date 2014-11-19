<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Form\NotificationType;
use PROCERGS\LoginCidadao\NotificationBundle\Model\NotificationInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Exception\InvalidFormException;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\OAuthBundle\Model\ClientInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Model\CategoryInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\PersonNotificationOption;
use PROCERGS\LoginCidadao\NotificationBundle\Model\NotificationSettings;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Category;
use PROCERGS\LoginCidadao\NotificationBundle\Model\BroadcastPlaceholder;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Placeholder;

class NotificationHandler implements NotificationHandlerInterface
{

    /** @var ObjectManager */
    private $om;
    private $entityClass;

    /** @var \PROCERGS\LoginCidadao\NotificationBundle\Entity\NotificationRepository */
    private $repository;
    private $formFactory;
    private $authenticatedHandlers = array();
    private $mailer;
    private $notificationType;

    public function __construct(ObjectManager $om, $entityClass,
                                FormFactoryInterface $formFactory, $mailer, $notificationType)
    {
        $this->om = $om;
        $this->entityClass = $entityClass;
        $this->repository = $this->om->getRepository($this->entityClass);
        $this->formFactory = $formFactory;
        $this->mailer = $mailer;
        $this->notificationType = $notificationType;
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
        $notification = $this->repository->find($id);
        if (!$notification->isRead()) {
            $notification->setReadDate(new DateTime());
            $this->om->persist($notification);
        }
        return $notification;
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
        $form = $this->formFactory->create($this->notificationType,
                                           $notification, compact('method'));
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {

            $notification = $form->getData();

            if (null !== $person && $notification->getPerson()->getId() !== $person->getId()) {
                throw new AccessDeniedHttpException();
            }
            if (null === $notification->getHtmlTemplate()) {
                $notification->setHtmlTemplate(self::renderHtmlByCategory($notification->getCategory(), $form->get('placeholders')->getData(), $notification->getTitle(), $notification->getShortText()));
            }
            $this->om->persist($notification);
            $this->om->flush($notification);
            
            $userSetting = $this->getSettings($notification->getPerson(),$notification->getCategory(), $notification->getCategory()->getClient());
            if ($userSetting && $userSetting[0]->getSendEmail()) {
                $html = self::_renderHtml($notification->getCategory()->getMailTemplate(), $form->get('placeholders')->getData(), $notification->getTitle(), $notification->getShortText());
                $this->mailer->sendEmailBasedOnNotification($notification->getCategory()->getMailSenderAddress(), $notification->getPerson()->getEmail(), $notification->getTitle(), $html);
            }

            return $notification;
        }

        throw new InvalidFormException('Invalid submitted data ' . print_r($this->getErrorMessages($form), 1) , $form);
    }
    
    private function getErrorMessages(\Symfony\Component\Form\Form $form) {
       $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            $template = $error->getMessageTemplate();
            $parameters = $error->getMessageParameters();
    
            foreach ($parameters as $var => $value) {
                $template = str_replace($var, $value, $template);
            }
    
            $errors[$key] = $template;
        }
        if ($form->count()) {
            foreach ($form as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        }
        return $errors;
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

    public function getAllFromPersonIdOffset(PersonInterface $person,
                                             $limit = 5, $offset = 0,
                                             ClientInterface $client = null)
    {
        return $this->repository->findNextNotifications($person, $limit,
                                                        $offset, $client);
    }

    public function countUnread(PersonInterface $person)
    {
        return $this->repository->getTotalUnread($person);
    }

    public function countUnreadByClient(PersonInterface $person)
    {
        return $this->repository->getTotalUnreadGroupByClient($person);
    }
    
    public static function renderHtmlByCategory($category, $replacePlaceholders=null, $replaceTitle=null, $replaceShortText=null)
    {
        $html = $category->getHtmlTemplate();
        return self::_renderHtml($html, $replacePlaceholders, $replaceTitle, $replaceShortText);
    }
    
    private static function _renderHtml($html, $replacePlaceholders=null, $replaceTitle=null, $replaceShortText=null)
    {
        if (null !== $replaceTitle) {
            $html = str_replace('%title%', $replaceTitle, $html);
        }
        if (null !== $replaceShortText) {
            $html = str_replace('%shorttext%', $replaceShortText, $html);
        }
        if (null !== $replacePlaceholders) {
            if (isset($replacePlaceholders[0]) && $replacePlaceholders[0] instanceof BroadcastPlaceholder) {
                foreach ($replacePlaceholders as $placeholder) {
                    $html = str_replace('%'.$placeholder->getName().'%', $placeholder->getValue(), $html);
                }
            } else if (isset($replacePlaceholders[0]) && $replacePlaceholders[0] instanceof Placeholder) {
                foreach ($replacePlaceholders as $placeholder) {
                    $html = str_replace('%'.$placeholder->getName().'%', $placeholder->getDefault(), $html);
                }
            } else {
                foreach ($replacePlaceholders as $name => $default) {
                    $html = str_replace('%'.$name.'%', $default, $html);
                }
            }
        }
        return $html;
    }
    
}
