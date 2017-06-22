<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Model;

use LoginCidadao\PhoneVerificationBundle\Model\DeliveryStatus;

class DeliveryStatusTest extends \PHPUnit_Framework_TestCase
{
    public function testIsFinal()
    {
        $this->assertTrue(DeliveryStatus::isFinal(DeliveryStatus::DELIVERED), 'DELIVERED');
        $this->assertTrue(DeliveryStatus::isFinal(DeliveryStatus::ERROR_IN_DELIVERY), 'ERROR_IN_DELIVERY');
        $this->assertTrue(DeliveryStatus::isFinal(DeliveryStatus::ERROR_IN_SEND), 'ERROR_IN_SEND');
        $this->assertTrue(DeliveryStatus::isFinal(DeliveryStatus::NOT_SEND), 'NOT_SEND');
        $this->assertTrue(
            DeliveryStatus::isFinal(DeliveryStatus::NO_DELIVERY_CONFIRMATION),
            'NO_DELIVERY_CONFIRMATION'
        );
        $this->assertTrue(DeliveryStatus::isFinal(DeliveryStatus::PROTOCOL_NOT_FOUND), 'PROTOCOL_NOT_FOUND');

        $this->assertFalse(DeliveryStatus::isFinal(DeliveryStatus::RECEIVED_REQUEST), 'RECEIVED_REQUEST');
        $this->assertFalse(DeliveryStatus::isFinal(DeliveryStatus::WAITING_TO_BE_DELIVERED), 'WAITING_TO_BE_DELIVERED');
        $this->assertFalse(DeliveryStatus::isFinal(DeliveryStatus::WAITING_TO_BE_SENT), 'WAITING_TO_BE_SENT');
        $this->assertFalse(DeliveryStatus::isFinal(DeliveryStatus::SENT), 'SENT');
    }

    public function testParse()
    {
        $texts = [
            'Aguardando envio',
            'Enviado',
            'Erro no envio',
            'Aguardando entrega',
            'Entregue',
            'Erro na entrega',
            'Protocolo não encontrado',
            'Protocolo nao encontrado',
            "Protocolo n�o encontrado",
            'Sem confirmação de entrega',
            'Sem confirmacao de entrega',
            "Sem confirma��o de entrega",
            'Não enviar (desenvolvimento)',
            "Nao enviar (desenvolvimento)",
            "N�o enviar (desenvolvimento)",
            'Requisição recebida',
            'Requisicao recebida',
            "Requisi��o recebida",
        ];

        foreach ($texts as $text) {
            $this->assertTrue(is_int(DeliveryStatus::parse($text)));
        }
    }

    public function testParseError()
    {
        $this->setExpectedException('LoginCidadao\PhoneVerificationBundle\Exception\InvalidSentVerificationStatusException');
        DeliveryStatus::parse('INVALID STATUS');
    }
}
