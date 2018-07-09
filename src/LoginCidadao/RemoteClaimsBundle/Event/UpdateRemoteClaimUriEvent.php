<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Event;

use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use Symfony\Component\EventDispatcher\Event;

class UpdateRemoteClaimUriEvent extends Event
{
    /**
     * @var TagUri
     */
    private $claimName;

    /**
     * @var string
     */
    private $newUri;

    /**
     * UpdateRemoteClaimUriEvent constructor.
     * @param TagUri $claimName
     * @param string $newUri
     */
    public function __construct(TagUri $claimName, $newUri)
    {
        $this->claimName = $claimName;
        $this->newUri = $newUri;
    }

    /**
     * @return TagUri
     */
    public function getClaimName()
    {
        return $this->claimName;
    }

    /**
     * @return string
     */
    public function getNewUri()
    {
        return $this->newUri;
    }
}
