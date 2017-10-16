<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Tests\Model;

use LoginCidadao\CoreBundle\Entity\PersonAddress;
use LoginCidadao\CoreBundle\Model\IdCardInterface;
use LoginCidadao\CoreBundle\Model\LocationSelectData;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;

class DynamicFormDataTest extends \PHPUnit_Framework_TestCase
{
    public function testModel()
    {
        /** @var PersonInterface $person */
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        /** @var IdCardInterface $idCard */
        $idCard = $this->getMock('LoginCidadao\CoreBundle\Model\IdCardInterface');

        /** @var LocationSelectData $placeOfBirth */
        $placeOfBirth = $this->getMock('LoginCidadao\CoreBundle\Model\LocationSelectData');

        /** @var PersonAddress $personAddress */
        $personAddress = $this->getMock('LoginCidadao\CoreBundle\Entity\PersonAddress');

        $idCardState = $this->getMock('LoginCidadao\CoreBundle\Entity\State');
        $scope = 'scope1 scope2';
        $state = 'Some State';
        $url = 'https://example.com';

        $model = new DynamicFormData();
        $model
            ->setPerson($person)
            ->setAddress($personAddress)
            ->setIdCard($idCard)
            ->setPlaceOfBirth($placeOfBirth)
            ->setScope($scope)
            ->setState($state)
            ->setRedirectUrl($url)
            ->setIdCardState($idCardState);

        $this->assertEquals($person, $model->getPerson());
        $this->assertEquals($personAddress, $model->getAddress());
        $this->assertEquals($idCard, $model->getIdCard());
        $this->assertEquals($placeOfBirth, $model->getPlaceOfBirth());
        $this->assertEquals($scope, $model->getScope());
        $this->assertEquals($state, $model->getState());
        $this->assertEquals($url, $model->getRedirectUrl());
        $this->assertEquals($idCardState, $model->getIdCardState());
    }
}
