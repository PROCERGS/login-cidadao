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

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use LoginCidadao\ValidationBundle\Validator\Constraints as LCAssert;

/**
 * Class SupportMessage represents a message sent by an user needing help.
 * @package LoginCidadao\CoreBundle\Model
 */
class SupportMessage
{
    const EXTRA_ID = 'contact.form.extra.id';
    const EXTRA_CREATED_AT = 'contact.form.extra.created_at';
    const EXTRA_EMAIL_CONFIRMED_AT = 'contact.form.extra.email_confirmed_at';
    const EXTRA_HAS_CPF = 'contact.form.extra.has_cpf.label';
    const EXTRA_HAS_CPF_YES = 'contact.form.extra.has_cpf.yes';
    const EXTRA_HAS_CPF_NO = 'contact.form.extra.has_cpf.no';

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
     * @LCAssert\Email(strict=true)
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
     * @var array
     */
    private $extra = [];

    /**
     * SupportMessage constructor.
     * @param PersonInterface|null $person
     */
    public function __construct(PersonInterface $person = null)
    {
        $this->extra = [];

        if ($person instanceof PersonInterface) {
            $this
                ->setName($person->getFullName())
                ->setEmail($person->getEmail())
                ->setExtra(self::EXTRA_ID, $person->getId())
                ->setExtra(self::EXTRA_CREATED_AT, $person->getCreatedAt()->format('Y-m-d H:i:s'))
                ->setExtra(self::EXTRA_EMAIL_CONFIRMED_AT, $person->getEmailConfirmedAt()->format('Y-m-d H:i:s'))
                ->setExtra(self::EXTRA_HAS_CPF, $person->getCpf() ? self::EXTRA_HAS_CPF_YES : self::EXTRA_HAS_CPF_NO);

            if ($person->getMobile() instanceof PhoneNumber) {
                $phoneUtil = PhoneNumberUtil::getInstance();
                $format = PhoneNumberFormat::E164;
                $this->setExtra('Mobile', $phoneUtil->format($person->getMobile(), $format));
            }
        }
    }

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

    /**
     * @param string $key
     * @param mixed $value
     * @return SupportMessage
     */
    public function setExtra($key, $value)
    {
        $this->extra[$key] = $value;

        return $this;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function getFormattedMessage(TranslatorInterface $translator)
    {
        $extras = array_filter($this->getExtra());
        $message = nl2br($this->getMessage());
        if (count($extras) > 0) {
            $extraHtml = array_map(function ($value, $key) use ($translator) {
                return sprintf("<strong>%s</strong>: %s", $translator->trans($key), $translator->trans($value));
            }, $extras, array_keys($extras));

            $extra = implode("<br>", $extraHtml);
            $message = "<p>{$message}</p><p>{$extra}</p>";
        }

        return $message;
    }
}
