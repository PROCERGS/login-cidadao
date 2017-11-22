<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\ValidationBundle\Validator\Constraints;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UrlValidator extends ConstraintValidator
{
    /**
     * Pattern extracted from https://regex101.com/r/BGxJ6n/1/codegen?language=php
     */
    const PATTERN = '/(?#URI)^(?#
    Scheme  )(?<scheme>[a-z][a-z0-9\+\-\.]*):(?#
    HeirPart)(?<HierPart>\/\/(?#
        Authority)(?<Authority>(?#
            UserInfo)((?<userInfo>(\%[0-9a-f][0-9a-f]|[a-z0-9\-\.\_\~]|[\!\$\&\'\(\)\*\+\,\;\=]|\:)*)\@)?(?#
            Host    )(?<host>(?#
                IP Literal)\[((?#
                    IPv6 Address     )(?<IPv6>((?<IPv6_1_R_H16>[0-9a-f]{1,4})\:){6,6}(?<IPV6_1_R_LS32>((?<IPV6_1_R_LS32_IPV4_DEC_OCTET>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3,3}(?<IPV6_1_R_LS32_IPV4_DEC_OCTET_>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])|(?<IPV6_1_R_LS32_H16_1>[0-9a-f]{1,4})\:(?<IPV6_1_R_LS32_H16_2>[0-9a-f]{1,4}))|\:\:((?<IPV6_2_R_H16>[0-9a-f]{1,4})\:){5,5}(?<IPV6_2_R_LS32>((?<IPV6_2_R_LS32_IPV4_DEC_OCTET>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3,3}(?<IPV6_2_R_LS32_IPV4_DEC_OCTET_>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])|(?<IPV6_2_R_LS32_H16_1>[0-9a-f]{1,4})\:(?<IPV6_2_R_LS32_H16_2>[0-9a-f]{1,4}))|(?<IPV6_3_L_H16>[0-9a-f]{1,4})?\:\:((?<IPV6_3_R_H16>[0-9a-f]{1,4})\:){4,4}(?<IPV6_3_R_LS32>((?<IPV6_3_R_LS32_IPV4_DEC_OCTET>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3,3}(?<IPV6_3_R_LS32_IPV4_DEC_OCTET_>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])|(?<IPV6_3_R_LS32_H16_1>[0-9a-f]{1,4})\:(?<IPV6_3_R_LS32_H16_2>[0-9a-f]{1,4}))|(((?<IPV6_4_L_H16_REPEAT>[0-9a-f]{1,4})\:)?(?<IPV6_4_L_H16>[0-9a-f]{1,4}))?\:\:((?<IPV6_4_R_H16>[0-9a-f]{1,4})\:){3,3}(?<IPV6_4_R_LS32>((?<IPV6_4_R_LS32_IPV4_DEC_OCTET>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3,3}(?<IPV6_4_R_LS32_IPV4_DEC_OCTET_>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])|(?<IPV6_4_R_LS32_H16_1>[0-9a-f]{1,4})\:(?<IPV6_4_R_LS32_H16_2>[0-9a-f]{1,4}))|(((?<IPV6_5_L_H16_REPEAT>[0-9a-f]{1,4})\:){0,2}(?<IPV6_5_L_H16>[0-9a-f]{1,4}))?\:\:((?<IPV6_5_R_H16>[0-9a-f]{1,4})\:){2,2}(?<IPV6_5_R_LS32>((?<IPV6_5_R_LS32_IPV4_DEC_OCTET>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3,3}(?<IPV6_5_R_LS32_IPV4_DEC_OCTET_>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])|(?<IPV6_5_R_LS32_H16_1>[0-9a-f]{1,4})\:(?<IPV6_5_R_LS32_H16_2>[0-9a-f]{1,4}))|(((?<IPV6_6_L_H16_REPEAT>[0-9a-f]{1,4})\:){0,3}(?<IPV6_6_L_H16>[0-9a-f]{1,4}))?\:\:(?<IPV6_6_R_H16>[0-9a-f]{1,4})\:(?<IPV6_6_R_LS32>((?<IPV6_6_R_LS32_IPV4_DEC_OCTET>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3,3}(?<IPV6_6_R_LS32_IPV4_DEC_OCTET_>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])|(?<IPV6_6_R_LS32_H16_1>[0-9a-f]{1,4})\:(?<IPV6_6_R_LS32_H16_2>[0-9a-f]{1,4}))|(((?<IPV6_7_L_H16_REPEAT>[0-9a-f]{1,4})\:){0,4}(?<IPV6_7_L_H16>[0-9a-f]{1,4}))?\:\:(?<IPV6_7_R_LS32>((?<IPV6_7_R_LS32_IPV4_DEC_OCTET>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3,3}(?<IPV6_7_R_LS32_IPV4_DEC_OCTET_>[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])|(?<IPV6_7_R_LS32_H16_1>[0-9a-f]{1,4})\:(?<IPV6_7_R_LS32_H16_2>[0-9a-f]{1,4}))|(((?<IPV6_8_L_H16_REPEAT>[0-9a-f]{1,4})\:){0,5}(?<IPV6_8_L_H16>[0-9a-f]{1,4}))?\:\:(?<IPV6_8_R_H16>[0-9a-f]{1,4})|(((?<IPV6_9_L_H16_REPEAT>[0-9a-f]{1,4})\:){0,6}(?<IPV6_9_L_H16>[0-9a-f]{1,4}))?\:\:)|(?#
                    IPvFuture Address)v[a-f0-9]+\.([a-z0-9\-\.\_\~]|[\!\$\&\'\(\)\*\+\,\;\=]|\:)+(?#
                ))\]|(?#
                IPv4 Address)(([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3,3}([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])|(?#
                RegName)([a-z0-9\-\.\_\~]|\%[0-9a-f][0-9a-f]|[\!\$\&\'\(\)\*\+\,\;\=])*(?#
            ))(?#
            Port    )(:(?<port>[0-9]+))?(?#
        ))(?#
        Path     )(?<path>(\/([a-z0-9\-\.\_\~\!\$\&\'\(\)\*\+\,\;\=\:\@]|(%[a-f0-9]{2,2}))*)*)(?#
    ))(?#
    Query   )(?<query>\?([a-z0-9\-\.\_\~\!\$\&\'\(\)\*\+\,\;\=\:\@\/\?]|(%[a-f0-9]{2,2}))*)?(?#
    Fragment)(?<fragment>#([a-z0-9\-\.\_\~\!\$\&\'\(\)\*\+\,\;\=\:\@\/\?]|(%[a-f0-9]{2,2}))*)?(?#
)$/imX';

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Uri) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Url');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = (string) $value;
        if ('' === $value) {
            return;
        }

        $pattern = static::PATTERN;

        if (!preg_match($pattern, $value)) {
            if ($this->context instanceof ExecutionContextInterface) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $this->formatValue($value))
                    ->setCode(Uri::INVALID_URL_ERROR)
                    ->addViolation();
            } else {
                $this->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $this->formatValue($value))
                    ->setCode(Uri::INVALID_URL_ERROR)
                    ->addViolation();
            }

            return;
        }

        if ($constraint->checkDNS) {
            $host = parse_url($value, PHP_URL_HOST);

            if (!is_string($host) || !checkdnsrr($host, 'ANY')) {
                if ($this->context instanceof ExecutionContextInterface) {
                    $this->context->buildViolation($constraint->dnsMessage)
                        ->setParameter('{{ value }}', $this->formatValue($host))
                        ->setCode(Uri::INVALID_URL_ERROR)
                        ->addViolation();
                } else {
                    $this->buildViolation($constraint->dnsMessage)
                        ->setParameter('{{ value }}', $this->formatValue($host))
                        ->setCode(Uri::INVALID_URL_ERROR)
                        ->addViolation();
                }
            }
        }
    }
}
