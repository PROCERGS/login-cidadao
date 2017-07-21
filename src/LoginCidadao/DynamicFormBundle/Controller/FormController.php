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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore
 */
class FormController extends Controller
{
    /**
     * @Route("/client/{clientId}/dynamic-form", name="client_dynamic_form")
     * @Template("LoginCidadaoCoreBundle:DynamicForm:edit.html.twig")
     */
    public function indexAction(Request $request, $clientId)
    {
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
     */
    public function locationFormAction(Request $request)
    {
        /** @var DynamicFormService $formService */
        $formService = $this->get('dynamic_form.service');

        $level = $request->get('level');
        $data = $formService->getLocationDataFromRequest($request);

        $formBuilder = $this->createFormBuilder($data, ['cascade_validation' => true]);
        $this->addPlaceOfBirth($formBuilder, $level);

        return array('form' => $formBuilder->getForm()->createView());
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
}
