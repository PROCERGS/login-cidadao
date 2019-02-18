<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\SupportBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;
use LoginCidadao\ValidationBundle\Validator\Constraints as LCAssert;

class PersonSearchRequest
{
    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $supportTicket;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     *
     * @var string
     */
    public $smartSearch;

    /**
     * @var string
     */
    public $phoneNumber;

    /**
     * @Assert\Email(checkHost=false, checkMX=false, strict=false)
     *
     * @var string
     */
    public $email;

    /**
     * @LCAssert\CPF()
     *
     * @var string
     */
    public $cpf;
}
