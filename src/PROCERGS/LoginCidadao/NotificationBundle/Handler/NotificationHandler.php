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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PROCERGS\LoginCidadao\NotificationBundle\NotificationEvents;
use PROCERGS\LoginCidadao\NotificationBundle\Event\NotificationEvent;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\FailedCallback;

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

    /** @var NotificationType */
    private $notificationType;

    /** @var EventDispatcherInterface */
    private $dispatcher;
    private $oauthDefaultClientUid;
    private $oauthDefaultClient;
    private $ch;
    private $proxy;

    public function __construct(ObjectManager $om, $entityClass,
                                FormFactoryInterface $formFactory, $mailer,
                                NotificationType $notificationType,
                                EventDispatcherInterface $dispatcher,
                                $oauthDefaultClientUid, $proxy)
    {
        $this->om                    = $om;
        $this->entityClass           = $entityClass;
        $this->repository            = $this->om->getRepository($this->entityClass);
        $this->formFactory           = $formFactory;
        $this->notificationType      = $notificationType;
        $this->dispatcher            = $dispatcher;
        $this->oauthDefaultClientUid = $oauthDefaultClientUid;
        $this->proxy                 = $proxy;
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
        $form = $this->formFactory->create($this->notificationType,
            $notification, compact('method'));
        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {

            $notification      = $form->getData();
            $notificationEvent = new NotificationEvent($notification);

            $this->dispatcher->dispatch(NotificationEvents::NOTIFICATION_INITIALIZE,
                $notificationEvent);

            if (null !== $person && $notification->getPerson()->getId() !== $person->getId()) {
                throw new AccessDeniedHttpException();
            }
            if (null === $notification->getHtmlTemplate()) {
                $notification->setHtmlTemplate(self::renderHtmlByCategory($notification->getCategory(),
                        $notification->getPlaceholders(),
                        $notification->getTitle(), $notification->getShortText()));
            }
            $this->om->persist($notification);
            $this->om->flush($notification);
            $this->dispatcher->dispatch(NotificationEvents::NOTIFICATION_SUCCESS,
                $notificationEvent);

            $this->dispatcher->dispatch(NotificationEvents::NOTIFICATION_COMPLETED,
                $notificationEvent);
            return $notification;
        }

        throw new InvalidFormException('Invalid submitted data '.print_r($this->getErrorMessages($form),
            1), $form);
    }

    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            $template   = $error->getMessageTemplate();
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
        $om               = $this->om;
        $categoriesRepo   = $om->getRepository('PROCERGSLoginCidadaoNotificationBundle:Category');
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
            return true;
        }
        return false;
    }

    public function markRangeAsRead(PersonInterface $person, $start, $end)
    {
        $om            = $this->om;
        $notifications = $om
            ->getRepository('PROCERGSLoginCidadaoNotificationBundle:Notification')
            ->findUntil($person, $start, $end);

        $result = array(
            'read' => array(),
            'failed' => array()
        );

        $this->initCallback();
        foreach ($notifications as $notification) {
            try {
                if (!$notification->isRead()) {
                    $notification->setRead(true);
                    $this->sendCallback($notification, $om);
                }
                $result['read'][] = $notification->getId();
            } catch (\Exception $e) {
                $result['failed'][] = $notification->getId();
            }
        }
        curl_close($this->ch);
        $om->flush();

        return $result;
    }

    private function initCallback()
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        if (!ini_get('open_basedir')) {
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        }
        $proxy = $this->proxy;
        if (isset($proxy['type'], $proxy['host'], $proxy['port'])) {
            curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxy['type']);
            curl_setopt($this->ch, CURLOPT_PROXY, $proxy['host']);
            curl_setopt($this->ch, CURLOPT_PROXYPORT, $proxy['port']);
            if (isset($proxy['auth'])) {
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxy['auth']);
            }
        }
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

    public static function renderHtmlByCategory($category,
                                                $replacePlaceholders = null,
                                                $replaceTitle = null,
                                                $replaceShortText = null)
    {
        $html = $category->getHtmlTemplate();
        return self::_renderHtml($html, $replacePlaceholders, $replaceTitle,
                $replaceShortText);
    }

    private static function _renderHtml($html, $replacePlaceholders = null,
                                        $replaceTitle = null,
                                        $replaceShortText = null)
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
                    $html = str_replace('%'.$placeholder->getName().'%',
                        $placeholder->getValue(), $html);
                }
            } else if (isset($replacePlaceholders[0]) && $replacePlaceholders[0] instanceof Placeholder) {
                foreach ($replacePlaceholders as $placeholder) {
                    $html = str_replace('%'.$placeholder->getName().'%',
                        $placeholder->getDefault(), $html);
                }
            } else {
                foreach ($replacePlaceholders as $name => $default) {
                    $html = str_replace('%'.$name.'%', $default, $html);
                }
            }
        }
        return $html;
    }

    public static function renderEmailByCategory($category,
                                                 $replacePlaceholders = null,
                                                 $replaceTitle = null,
                                                 $replaceShortText = null)
    {
        return self::_renderHtml($category->getMailTemplate(),
                $replacePlaceholders, $replaceTitle, $replaceShortText);
    }

    public function getEmailHtml(NotificationInterface $notification)
    {
        return self::_renderHtml($notification->getCategory()->getMailTemplate(),
                $notification->getPlaceholders(), $notification->getTitle(),
                $notification->getShortText());
    }

    public function getLoginCidadaoClient()
    {
        if ($this->oauthDefaultClient === null) {
            $this->oauthDefaultClient = $this->om->getRepository('PROCERGSOAuthBundle:Client')->findOneBy(array(
                'uid' => $this->oauthDefaultClientUid));
        }
        return $this->oauthDefaultClient;
    }

    protected function registerFailedCallback(NotificationInterface $notification,
                                              ObjectManager $om, $ch,
                                              $curlResponse)
    {
        $info = curl_getinfo($ch);

        $body = null;
        if ($curlResponse !== false) {
            $body = $curlResponse;
        }

        $failedCallback = new FailedCallback();
        $failedCallback->setDate(new \DateTime());
        $failedCallback->setNotification($notification);
        $failedCallback->setRequestUrl($info['url']);
        $failedCallback->setResponseBody($body);
        $failedCallback->setResponseCode($info['http_code']);
        $om->persist($failedCallback);
    }

    private function sendCallback(NotificationInterface $notification,
                                  ObjectManager $om)
    {
        if ($notification->getCallbackUrl()) {
            $secret            = $notification->getCategory()->getClient()->getSecret();
            $base['data']      = json_encode(array(
                'id' => $notification->getId(),
                'person_id' => $notification->getPerson()->getId(),
                'read_date' => $notification->getReadDate()->getTimestamp()
            ));
            $base['signature'] = hash_hmac('sha256', $base['data'], $secret);
            $dataPost          = http_build_query($base);
            curl_setopt($this->ch, CURLOPT_URL, $notification->getCallbackUrl());
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $dataPost);
            $curlResponse      = curl_exec($this->ch);
            $code              = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($code != '200') {
                $this->registerFailedCallback($notification, $om, $this->ch,
                    $curlResponse);
            }
        }
    }

    public function getUnread(PersonInterface $person, $limit = 5)
    {
        return $this->repository->getUnread($person, $limit);
    }
}
