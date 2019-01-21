<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\BadgesControlBundle\Tests;

use LoginCidadao\BadgesControlBundle\Model\BadgeInterface;

class BadgeMocker
{
    public static function getBadge($namespace, $name): BadgeInterface
    {
        return new class($namespace, $name) implements BadgeInterface
        {
            private $namespace;
            private $name;

            public function __construct(string $namespace, string $name)
            {
                $this->namespace = $namespace;
                $this->name = $name;
            }

            public function getNamespace()
            {
                return $this->namespace;
            }

            public function getName()
            {
                return $this->name;
            }

            public function getData()
            {
                return true;
            }

            public function __toString(): string
            {
                return "{$this->getNamespace()}.{$this->getName()}";
            }
        };
    }
}
