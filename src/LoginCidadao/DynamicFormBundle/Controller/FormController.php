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

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\DynamicFormBundle\Service\DynamicFormService;
use LoginCidadao\DynamicFormBundle\Service\DynamicFormServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore
 */
class FormController extends Controller
{
    /**
     * @Route("/dynamic-form", name="dynamic_form")
     * @Route("/client/{clientId}/dynamic-form", name="legacy_client_dynamic_form")
     * @Template("LoginCidadaoDynamicFormBundle:Form:edit.html.twig")
     *
     * @param Request $request
     * @param string|null $clientId
     * @return array|RedirectResponse
     */
    public function indexAction(Request $request, $clientId = null)
    {
        if (!$clientId) {
            $clientId = $request->get('client_id');
        }
        $scope = explode(' ', $request->get('scope', null));

        /** @var DynamicFormServiceInterface $formService */
        $formService = $this->get('dynamic_form.service');

        /** @var PersonInterface $person */
        $person = $this->getUser();

        $data = $formService->getDynamicFormData($person, $request, $request->get('scope', null));
        $type = 'LoginCidadao\DynamicFormBundle\Form\DynamicFormType';
        $form = $this->createForm($type, $data, ['dynamic_form_service' => $formService]);

        $result = $formService->processForm($form, $request);

        if ($result['response'] instanceof RedirectResponse) {
            return $result['response'];
        }

        return [
            'client' => $formService->getClient($clientId),
            'form' => $form->createView(),
            'scope' => $scope,
            'skipUrl' => $formService->getSkipUrl($data),
        ];
    }

    /**
     * @Route("/dynamic-form/location", name="dynamic_form_location")
     * @Template()
     *
     * @param Request $request
     * @return array
     */
    public function locationFormAction(Request $request)
    {
        /** @var DynamicFormService $formService */
        $formService = $this->get('dynamic_form.service');

        $level = $request->get('level');
        $data = $formService->getLocationDataFromRequest($request);

        $form = $this->createFormBuilder($data, ['cascade_validation' => true])->getForm();
        $this->addPlaceOfBirth($form, $level);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/dynamic-form/skip", name="dynamic_form_skip")
     *
     * @param Request $request
     * @return Response
     */
    public function skipAction(Request $request)
    {
        /** @var DynamicFormService $formService */
        $formService = $this->get('dynamic_form.service');
        $defaultResponse = $this->redirectToRoute('lc_dashboard');

        return $formService->skipCurrent($request, $defaultResponse);
    }

    private function addPlaceOfBirth(FormInterface $form, $level)
    {
        $form->add(
            'placeOfBirth',
            'LoginCidadao\CoreBundle\Form\Type\CitySelectorComboType',
            [
                'level' => $level,
                'city_label' => 'Place of birth - City',
                'state_label' => 'Place of birth - State',
                'country_label' => 'Place of birth - Country',
            ]
        );

        return;
    }
}
