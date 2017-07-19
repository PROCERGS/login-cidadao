<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Service;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

interface DynamicFormServiceInterface
{
    /**
     * @param PersonInterface $person
     * @param Request $request
     * @param string $scope
     * @return DynamicFormData
     */
    public function getDynamicFormData(PersonInterface $person, Request $request, $scope);

    /**
     * @param FormInterface $builder
     * @param PersonInterface $person
     * @param array $scopes
     * @return FormInterface
     */
    public function buildForm(FormInterface $builder, PersonInterface $person, array $scopes);

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return FormInterface
     */
    public function processForm(FormInterface $form, Request $request);

    /**
     * @param $clientId
     * @return ClientInterface
     */
    public function getClient($clientId);
}
