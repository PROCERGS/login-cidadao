<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Model;

use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\SupportMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

class SupportMessageTest extends TestCase
{
    public function testSimpleSupportMessage()
    {
        $message = (new SupportMessage())
            ->setName($name = 'Name Here')
            ->setEmail($email = 'email@example.com')
            ->setMessage($text = 'Message');

        $this->assertSame($name, $message->getName());
        $this->assertSame($email, $message->getEmail());
        $this->assertSame($text, $message->getMessage());
    }

    /**
     * @throws \ReflectionException
     */
    public function testCompleteSupportMessage()
    {
        /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->exactly(10))->method('trans')->willReturnCallback(function ($text) {
            return $text;
        });

        $mobile = (new PhoneNumber())
            ->setCountryCode(55)
            ->setNationalNumber('51999999999');

        $person = (new Person())
            ->setFirstName($firstName = 'Fulano')
            ->setSurname($lastName = 'de Tal')
            ->setEmail($email = 'email@example.com')
            ->setCreatedAt($createdAt = new \DateTime())
            ->setEmailConfirmedAt($emailConfirmedAt = new \DateTime())
            ->setMobile($mobile);

        $reflection = new \ReflectionClass($person);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($person, '123');

        $message = (new SupportMessage($person))
            ->setMessage('My message');
        $formattedMessage = $message->getFormattedMessage($translator);

        $this->assertContains('My message', $formattedMessage);
        $this->assertContains(SupportMessage::EXTRA_HAS_CPF_NO, $formattedMessage);
        $this->assertContains(SupportMessage::EXTRA_EMAIL_CONFIRMED_AT, $formattedMessage);
        $this->assertContains(SupportMessage::EXTRA_CREATED_AT, $formattedMessage);
        $this->assertContains(SupportMessage::EXTRA_ID, $formattedMessage);
    }
}
