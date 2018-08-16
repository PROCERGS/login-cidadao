<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Exception;

use Throwable;

class CpfNotSubscribedToNfgException extends \RuntimeException
{
    public const ERROR_CODE = 'cpf_not_subscribed_to_nfg';

    /** @var string */
    private $cpf;

    public function __construct(string $cpf, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->cpf = $cpf;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getCpf(): string
    {
        return $this->cpf;
    }
}
