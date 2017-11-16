<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Tests\Helper;

use LoginCidadao\OAuthBundle\Helper\ScopeFinderHelper;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ScopeFinderHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetScopeFromRequest()
    {
        $request = $this->getRequest();
        $request->request = new ParameterBag(['scope' => 'openid name']);

        $helper = new ScopeFinderHelper($request);
        $scope = $helper->getScope();
        $this->checkScope($scope);
    }

    public function testGetScopeFromQuery()
    {
        $request = $this->getRequest();
        $request->request = new ParameterBag();
        $request->query = new ParameterBag(['scope' => 'openid name']);

        $helper = new ScopeFinderHelper($request);
        $scope = $helper->getScope();
        $this->checkScope($scope);
    }

    public function testGetScopeFromForm()
    {
        $formName = 'some_name';

        $request = $this->getRequest();
        $request->request = new ParameterBag(["{$formName}" => ['scope' => 'openid name']]);
        $request->query = new ParameterBag();

        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())->method('getName')->willReturn($formName);

        $helper = new ScopeFinderHelper($request, $form);
        $scope = $helper->getScope();
        $this->checkScope($scope);
    }

    public function testScopeNotFound()
    {
        $request = $this->getRequest();
        $request->request = new ParameterBag();
        $request->query = new ParameterBag();

        $helper = new ScopeFinderHelper($request);
        $scope = $helper->getScope();
        $this->assertNull($scope);
    }

    public function testInvalidConstructorCall()
    {
        $this->setExpectedException('\InvalidArgumentException');
        new ScopeFinderHelper('error');
    }

    public function testConstructorWithRequestStack()
    {
        $request = $this->getRequest();
        $request->request = new ParameterBag(['scope' => 'openid name']);

        $stack = new RequestStack();
        $stack->push($request);

        $helper = new ScopeFinderHelper($stack);
        $scope = $helper->getScope();
        $this->checkScope($scope);
    }

    /**
     * @return Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRequest()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()->getMock();

        return $request;
    }

    private function checkScope($scope)
    {
        $this->assertCount(2, $scope);
        $this->assertContains('openid', $scope);
        $this->assertContains('name', $scope);
    }
}
