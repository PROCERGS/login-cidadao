<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Diagnostics;

use Predis\ClientInterface;

interface RedisClientMockInterface extends ClientInterface
{
    public function get($key);

    public function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null);

    public function del(array $keys);
}
