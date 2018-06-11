<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Service;

use LoginCidadao\CoreBundle\Service\PasswordHintService;
use PHPUnit\Framework\TestCase;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordRequirements;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PasswordHintServiceTest extends TestCase
{

    public function testGetHintStringNoLimits()
    {
        $userClass = 'UserClass';
        $service = new PasswordHintService($this->getValidator(), $this->getTranslator(), $userClass);
        $service->getHintString();
    }

    public function testGetHintStringRange()
    {
        $messages = [
            ['password_hint.range.no_reqs'],
        ];
        $userClass = 'UserClass';
        $service = new PasswordHintService($this->getValidator(), $this->getTranslator($messages), $userClass);
        $service->getHintString(['min' => 8, 'max' => 160]);
    }

    public function testGetHintStringMin()
    {
        $messages = [
            ['password_hint.min.no_reqs'],
        ];
        $userClass = 'UserClass';
        $service = new PasswordHintService($this->getValidator(), $this->getTranslator($messages), $userClass);
        $service->getHintString(['min' => 8]);
    }

    public function testGetHintStringMax()
    {
        $messages = [
            ['password_hint.max.no_reqs'],
        ];
        $userClass = 'UserClass';
        $service = new PasswordHintService($this->getValidator(), $this->getTranslator($messages), $userClass);
        $service->getHintString(['max' => 200]);
    }

    public function testGetHintStringNoLimitsFullReqs()
    {
        $messages = [
            ['password_hint.no_limit.with_reqs'],
            ['password_hint.requirements.numbers'],
            ['password_hint.requirements.letters'],
            ['password_hint.requirements.special'],
            ['password_hint.and'],
        ];
        $userClass = 'UserClass';
        $service = new PasswordHintService($this->getValidator(), $this->getTranslator($messages), $userClass);
        $service->getHintString([
            'requireLetters' => true,
            'requireNumbers' => true,
            'requireSpecialCharacter' => true,
        ]);
    }

    public function testNoMetadata()
    {
        $expected = [
            'min' => 0,
            'max' => null,
            'requireLetters' => false,
            'requireNumbers' => false,
            'requireSpecialCharacter' => false,
        ];

        $userClass = 'UserClass';
        $service = new PasswordHintService($this->getValidator(), $this->getTranslator(), $userClass);
        $this->assertSame($expected, $service->getPasswordRequirements());
    }

    public function testNoPassword()
    {
        $expected = [
            'min' => 0,
            'max' => null,
            'requireLetters' => false,
            'requireNumbers' => false,
            'requireSpecialCharacter' => false,
        ];

        $userClass = 'UserClass';

        $this->runGetRequirementsTest(false, [], $userClass, $expected);

        $metadata = $this->createMock('Symfony\Component\Validator\Mapping\ClassMetadataInterface');
        $metadata->expects($this->once())
            ->method('hasPropertyMetadata')->with('plainPassword')->willReturn(false);

        $validator = $this->getValidator();
        $validator->expects($this->once())
            ->method('getMetadataFor')->with($userClass)
            ->willReturn($metadata);

        $service = new PasswordHintService($validator, $this->getTranslator(), $userClass);
        $this->assertSame($expected, $service->getPasswordRequirements());
    }

    public function testGetPasswordRequirements()
    {
        $expected = [
            'min' => 8,
            'max' => 256,
            'requireLetters' => true,
            'requireNumbers' => true,
            'requireSpecialCharacter' => true,
        ];

        $userClass = 'UserClass';

        $constraints = [
            new PasswordRequirements([
                'minLength' => 8,
                'requireLetters' => true,
                'requireCaseDiff' => true,
                'requireNumbers' => true,
                'requireSpecialCharacter' => true,
            ]),
            new Length(['min' => 6, 'max' => 256]), // 'min' should be overridden
        ];

        $this->runGetRequirementsTest(true, $constraints, $userClass, $expected);
    }

    public function testGetPasswordRequirementsNoMin()
    {
        $expected = [
            'min' => 0,
            'max' => null,
            'requireLetters' => false,
            'requireNumbers' => false,
            'requireSpecialCharacter' => false,
        ];

        $userClass = 'UserClass';

        $constraints = [new NotBlank()];

        $this->runGetRequirementsTest(true, $constraints, $userClass, $expected);
    }

    /**
     * @param array $expectedMessagesMap
     * @return TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTranslator($expectedMessagesMap = [])
    {
        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->exactly(count($expectedMessagesMap)))
            ->method('trans')->willReturnMap($expectedMessagesMap);

        return $translator;
    }

    /**
     * @return ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getValidator()
    {
        return $this->createMock('Symfony\Component\Validator\Validator\ValidatorInterface');
    }

    private function runGetRequirementsTest(
        $hasProp,
        $constraints,
        $userClass,
        $expected
    ) {
        $metadata = $this->createMock('Symfony\Component\Validator\Mapping\ClassMetadataInterface');
        $metadata->expects($this->once())
            ->method('hasPropertyMetadata')->with('plainPassword')->willReturn($hasProp);

        if ($hasProp) {
            $propMetadata = $this->createMock('Symfony\Component\Validator\Mapping\PropertyMetadataInterface');
            $propMetadata->expects($this->once())
                ->method('getConstraints')->willReturn($constraints);

            $metadata->expects($this->once())
                ->method('getPropertyMetadata')->with('plainPassword')->willReturn([$propMetadata]);
        }

        $validator = $this->getValidator();
        $validator->expects($this->once())
            ->method('getMetadataFor')->with($userClass)
            ->willReturn($metadata);

        $service = new PasswordHintService($validator, $this->getTranslator(), $userClass);
        $this->assertSame($expected, $service->getPasswordRequirements());
    }
}
