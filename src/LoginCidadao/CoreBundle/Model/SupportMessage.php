<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SupportMessage represents a message sent by an user needing help.
 * @package LoginCidadao\CoreBundle\Model
 */
class SupportMessage
{
    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="person.validation.name.length.min",
     *     maxMessage="person.validation.name.length.max"
     * )
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Email(strict=true)
     * @Assert\Length(
     *     max="255",
     *     maxMessage="person.validation.email.length.max"
     * )
     */
    private $email;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $message;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return SupportMessage
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return SupportMessage
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     * @return SupportMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}
