<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Model;

// TODO: this should be in the sms-service lib!!
use LoginCidadao\PhoneVerificationBundle\Exception\InvalidSentVerificationStatusException;

final class DeliveryStatus
{
    // non-final:
    const RECEIVED_REQUEST = 9;

    // final:
    const WAITING_TO_BE_SENT = 0;
    const SENT = 1;
    const ERROR_IN_SEND = 2;
    const WAITING_TO_BE_DELIVERED = 3;
    const DELIVERED = 4;
    const ERROR_IN_DELIVERY = 5;
    const PROTOCOL_NOT_FOUND = 6;
    const NO_DELIVERY_CONFIRMATION = 7;
    const NOT_SEND = 8;

    public static function isFinal($state)
    {
        switch ($state) {
            case self::DELIVERED:
            case self::ERROR_IN_DELIVERY:
            case self::ERROR_IN_SEND:
            case self::NOT_SEND:
            case self::NO_DELIVERY_CONFIRMATION:
            case self::PROTOCOL_NOT_FOUND:
                return true;
            default:
                return false;
        }
    }

    public static function parse($state)
    {
        switch ($state) {
            case 'Aguardando envio':
                return self::WAITING_TO_BE_SENT;
            case 'Enviado':
                return self::SENT;
            case 'Erro no envio':
                return self::ERROR_IN_SEND;
            case 'Aguardando entrega':
                return self::WAITING_TO_BE_DELIVERED;
            case 'Entregue':
                return self::DELIVERED;
            case 'Erro na entrega':
                return self::ERROR_IN_DELIVERY;
            case 'Protocolo não encontrado':
            case 'Protocolo nao encontrado':
            case "Protocolo n�o encontrado":
                return self::PROTOCOL_NOT_FOUND;
            case 'Sem confirmação de entrega':
            case 'Sem confirmacao de entrega':
            case "Sem confirma��o de entrega":
                return self::NO_DELIVERY_CONFIRMATION;
            case 'Não enviar (desenvolvimento)':
            case "Nao enviar (desenvolvimento)":
            case "N�o enviar (desenvolvimento)":
                return self::NOT_SEND;
            case 'Requisição recebida':
            case 'Requisicao recebida':
            case "Requisi��o recebida":
                return self::RECEIVED_REQUEST;
            default:
                throw new InvalidSentVerificationStatusException("The status '{$state}' is not valid!");
        }
    }
}
