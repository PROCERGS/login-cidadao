<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Controller;

use LoginCidadao\PhoneVerificationBundle\Service\SmsStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @codeCoverageIgnore
 */
class StatisticsController extends Controller
{
    /**
     * @Route("/api/v1/phone-verification/update-status.{_format}",
     *     name="api_v1_phone_verification_update_status",
     *     defaults={"_format"="json"}
     * )
     * @Template()
     */
    public function updateAction()
    {
        $em = $this->getDoctrine()->getManager();
        $dispatcher = $this->get('event_dispatcher');
        $sentVerificationRepo = $em->getRepository('LoginCidadaoPhoneVerificationBundle:SentVerification');

        $smsUpdater = new SmsStatusService($dispatcher, $sentVerificationRepo);
        $transactionsUpdated = $smsUpdater->updateSentVerificationStatus($em);

        return new JsonResponse(['transactions_updated' => $transactionsUpdated, 'count' => count($transactionsUpdated)]);
    }

    /**
     * @Route("/api/v1/phone-verification/average-delivery-time.{_format}",
     *     name="api_v1_phone_verification_average_delivery_time",
     *     defaults={"_format"="json"}
     * )
     * @Method("GET")
     * @Template()
     */
    public function getDeliveryAverageAction(Request $request)
    {
        $amount = $request->get('amount', 10);

        $em = $this->getDoctrine()->getManager();
        $dispatcher = $this->get('event_dispatcher');
        $sentVerificationRepo = $em->getRepository('LoginCidadaoPhoneVerificationBundle:SentVerification');

        $smsUpdater = new SmsStatusService($dispatcher, $sentVerificationRepo);
        $average = $smsUpdater->getAverageDeliveryTime($amount);

        return new JsonResponse(['average_delivery_time' => $average, 'unit' => 'seconds']);
    }

    /**
     * @Route("/api/v1/phone-verification/not-delivered.{_format}",
     *     name="api_v1_phone_verification_not_delivered",
     *     defaults={"_format"="json"}
     * )
     * @Method("GET")
     * @Template()
     */
    public function getNotDeliveredAction(Request $request)
    {
        $seconds = $request->get('seconds', null);
        $seconds = is_numeric($seconds) ? (int)$seconds : null;

        $em = $this->getDoctrine()->getManager();
        $dispatcher = $this->get('event_dispatcher');
        $sentVerificationRepo = $em->getRepository('LoginCidadaoPhoneVerificationBundle:SentVerification');

        $smsUpdater = new SmsStatusService($dispatcher, $sentVerificationRepo);

        if ($seconds === null) {
            $seconds = $smsUpdater->getAverageDeliveryTime(10);
        }

        $notDelivered = $smsUpdater->getDelayedDeliveryTransactions($seconds);

        return new JsonResponse(['delayed_transactions' => $notDelivered, 'count' => count($notDelivered), 'max_delay' => $seconds]);
    }
}
