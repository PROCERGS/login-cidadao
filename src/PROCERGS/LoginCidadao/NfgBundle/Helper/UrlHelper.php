<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Helper;


class UrlHelper
{
    public static function addToQuery(array $data, $query = null)
    {
        if (!$query) {
            $query = [];
        }
        if ($query && !is_array($query)) {
            parse_str($query, $query);
        }

        return http_build_query(array_merge($data, $query));
    }
}
