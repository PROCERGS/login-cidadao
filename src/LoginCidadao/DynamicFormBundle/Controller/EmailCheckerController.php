<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Controller;

use LoginCidadao\APIBundle\Exception\RequestTimeoutException;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\LongPolling\LongPollingUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore
 */
class EmailCheckerController extends Controller
{
    /**
     * @Route("/wait/validate/email", name="wait_valid_email")
     * @Method("GET")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkEmailAction(Request $request)
    {
        /** @var PersonInterface $user */
        $user = $this->getUser();

        /** @var LongPollingUtils $longPolling */
        $longPolling = $this->get('long_polling');

        $updatedAt = \DateTime::createFromFormat('Y-m-d H:i:s', $request->get('updated_at'));
        if (!$updatedAt) {
            $updatedAt = new \DateTime();
        }

        try {
            $response = $longPolling->waitValidEmail($user, $updatedAt);

            return new JsonResponse($response);
        } catch (RequestTimeoutException $e) {
            return new JsonResponse(false, Response::HTTP_REQUEST_TIMEOUT);
        }
    }
}
