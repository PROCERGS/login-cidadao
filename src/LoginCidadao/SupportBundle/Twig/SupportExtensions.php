<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\SupportBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SupportExtensions extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('support_mask', [$this, 'mask']),
        ];
    }

    public function mask($value, bool $mask = true, $hideLength = false)
    {
        if (!$mask) {
            return $value;
        }

        if (!$hideLength) {
            return preg_replace('/\w/', '*', $value);
        } else {
            return str_repeat('*', $hideLength);
        }
    }
}
