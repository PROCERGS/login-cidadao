<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Tests\Form;

use LoginCidadao\CoreBundle\Entity\IdCard;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\DynamicFormBundle\Form\DynamicFormBuilder;
use LoginCidadao\DynamicFormBundle\Model\DynamicFormData;
use LoginCidadao\ValidationControlBundle\Handler\ValidationHandler;
use Symfony\Component\Form\FormInterface;

class DynamicFormBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testAddFieldFromScope()
    {
        $state = new State();
        $state->setId(1);

        $idCard = new IdCard();
        $idCard->setState($state);
        $idCards = [
            $idCard,
        ];

        /** @var PersonInterface|\PHPUnit_Framework_MockObject_MockObject $person */
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->atLeastOnce())->method('getIdCards')->willReturn($idCards);

        $fields = [];
        $data = new DynamicFormData();
        $data->setState($state);
        $data->setIdCardState($state);
        $data->setPerson($person);

        $form = $this->getForm($fields);

        /** @var ValidationHandler|\PHPUnit_Framework_MockObject_MockObject $validationHandler */
        $validationHandler = $this->getMockBuilder('LoginCidadao\ValidationControlBundle\Handler\ValidationHandler')
            ->disableOriginalConstructor()->getMock();
        $builder = new DynamicFormBuilder($validationHandler);

        $scopes = ['name', 'email', 'cpf', 'birthdate', 'city', 'phone_number', 'id_cards', 'addresses', 'other_claim'];

        foreach ($scopes as $scope) {
            $builder->addFieldFromScope($form, $scope, $data);
        }

        $this->assertNotEmpty($fields);
        $this->assertContains('firstname', $fields);
        $this->assertContains('surname', $fields);
        $this->assertContains('cpf', $fields);
        $this->assertContains('email', $fields);
        $this->assertContains('mobile', $fields);
        $this->assertContains('birthdate', $fields);
        $this->assertContains('placeOfBirth', $fields);
        $this->assertContains('idcard', $fields);
    }

    public function testInstantiateIdCard()
    {
        $fields = [];
        $state = new State();
        $state->setId(1);

        $idCard = new IdCard();
        $idCard->setState($state);

        /** @var PersonInterface|\PHPUnit_Framework_MockObject_MockObject $person */
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->atLeastOnce())->method('getIdCards')->willReturn([]);

        $data = new DynamicFormData();
        $data->setIdCardState($state)
            ->setPerson($person);

        $form = $this->getForm($fields);

        /** @var ValidationHandler|\PHPUnit_Framework_MockObject_MockObject $validationHandler */
        $validationHandler = $this
            ->getMockBuilder('LoginCidadao\ValidationControlBundle\Handler\ValidationHandler')
            ->disableOriginalConstructor()->getMock();
        $validationHandler->expects($this->once())
            ->method('instantiateIdCard')->with($state)
            ->willReturn($idCard);

        $builder = new DynamicFormBuilder($validationHandler);

        $scopes = ['id_cards'];

        foreach ($scopes as $scope) {
            $builder->addFieldFromScope($form, $scope, $data);
        }

        $this->assertNotEmpty($fields);
        $this->assertContains('idcard', $fields);
    }

    public function testIdCardWithoutState()
    {
        $fields = [];
        $state = new State();
        $state->setId(1);

        $idCard = new IdCard();
        $idCard->setState($state);

        /** @var PersonInterface|\PHPUnit_Framework_MockObject_MockObject $person */
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->never())->method('getIdCards');

        $data = new DynamicFormData();
        $data->setPerson($person);

        $form = $form = $this->getMock('Symfony\Component\Form\FormInterface');

        /** @var ValidationHandler|\PHPUnit_Framework_MockObject_MockObject $validationHandler */
        $validationHandler = $this
            ->getMockBuilder('LoginCidadao\ValidationControlBundle\Handler\ValidationHandler')
            ->disableOriginalConstructor()->getMock();
        $validationHandler->expects($this->never())
            ->method('instantiateIdCard');

        $builder = new DynamicFormBuilder($validationHandler);

        $scopes = ['id_cards'];

        foreach ($scopes as $scope) {
            $builder->addFieldFromScope($form, $scope, $data);
        }

        $this->assertEmpty($fields);
        $this->assertNotContains('idcard', $fields);
    }

    private function getForm(&$fields)
    {
        $addField = function ($field) use (&$fields) {
            $fields[] = $field;
        };

        /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form */
        $personForm = $this->getMock('Symfony\Component\Form\FormInterface');
        $personForm->expects($this->any())->method('add')->willReturnCallback($addField);

        /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form */
        $form = $this->getMock('Symfony\Component\Form\FormInterface');

        $form->expects($this->atLeastOnce())->method('add')->willReturnCallback($addField);
        $form->expects($this->any())->method('get')->willReturnCallback(
            function ($field) use (&$fields, &$personForm) {
                if ($field == 'person') {
                    return $personForm;
                }

                return $field;
            }
        );
        $form->expects($this->any())->method('has')->willReturnCallback(
            function ($field) use (&$fields) {
                return array_key_exists($field, $fields);
            }
        );

        return $form;
    }
}
