<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Model;

use Egulias\EmailValidator\EmailValidator;
use League\Uri\Components\Host;
use League\Uri\Interfaces\Uri;
use League\Uri\Schemes\Generic\AbstractHierarchicalUri;

class TagUri extends AbstractHierarchicalUri implements Uri
{
    const REGEX_DNScomp = '(?:[\w\d](?:[\w\d-]*[\w\d])?)';
    const REGEX_date = '(?:\d{4}(?:-\d{2}(?:-\d{2})?)?)';

    protected static $supportedSchemes = ['tag'];

    public function getAuthorityName()
    {
        $path = $this->getPath();
        $taggingEntity = $this->getTaggingEntityRegex();
        if (preg_match("/^{$taggingEntity}/", $path, $m) !== 1) {
            throw new \InvalidArgumentException('Invalid taggingEntity');
        }

        return $m['authorityName'];
    }

    protected function isValid()
    {
        $authorityName = $this->getAuthorityName();
        if (strstr($authorityName, '@') === false) {
            $validAuthorityName = new Host($authorityName);
        } else {
            $validator = new EmailValidator();
            $validAuthorityName = $validator->isValid($authorityName);
        }

        return !$this->userInfo->__toString()
            && !$this->host->__toString()
            && $validAuthorityName;
    }

    private function getDnsNameRegex()
    {
        return '(?:'.self::REGEX_DNScomp.'(?:[.]'.self::REGEX_DNScomp.')*)';
    }

    private function getTaggingEntityRegex()
    {
        return '(?<authorityName>'.$this->getDnsNameRegex().'|'.$this->getEmailAddressRegex().'),(?<date>'.self::REGEX_date.')';
    }

    private function getEmailAddressRegex()
    {
        return '(?:[\w\d-._+]*@'.$this->getDnsNameRegex().')';
    }
}
