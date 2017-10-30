<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Helper;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ScopeFinderHelper
{
    /** @var Request */
    private $request;

    /** @var FormInterface */
    private $form;

    public function __construct($request, FormInterface $form = null)
    {
        $this->form = $form;
        if ($request instanceof RequestStack) {
            $this->request = $request->getCurrentRequest();
        } elseif ($request instanceof Request) {
            $this->request = $request;
        } else {
            throw new \InvalidArgumentException('$request must be either a Request or a RequestStack');
        }
    }

    /**
     * @return array|null
     */
    public function getScope()
    {
        $scope = $this->request->request->get('scope', false);

        if (!$scope) {
            $scope = $this->request->query->get('scope', false);
        }

        if (!$scope) {
            if (!$this->form) {
                return null;
            }
            $form = $this->form->getName();
            $scope = $this->request->request->get("{$form}[scope]", false, true);
        }

        return !is_array($scope) ? explode(' ', $scope) : $scope;
    }
}
