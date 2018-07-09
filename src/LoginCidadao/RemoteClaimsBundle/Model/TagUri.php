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
use Egulias\EmailValidator\Validation\RFCValidation;
use Psr\Http\Message\UriInterface;

class TagUri implements UriInterface
{
    const REGEX_OVERALL = '^tag:(?<taggingEntity>[^:]+):(?<specific>[^#]+)(?:#(?<fragment>[\w\d&?@:_]+))?$';
    const REGEX_DATE = '\d{4}(?:-(?:0\d|1[012])(?:-(?:[012]\d|3[01]))?)?';
    const REGEX_DATE_YEAR = '(?<year>\d{4})';
    const REGEX_DATE_MONTH = '(?<month>0\d|1[012])';
    const REGEX_DATE_DAY = '(?<day>[012]\d|3[01])';
    const REGEX_DNScomp = '(?:[\w\d](?:[\w\d-]*[\w\d])?)';
    const REGEX_date = '(?:\d{4}(?:-\d{2}(?:-\d{2})?)?)';

    protected static $supportedSchemes = ['tag'];

    /** @var string */
    private $authorityName;

    /** @var string */
    private $date;

    /** @var string */
    private $specific;

    /** @var string */
    private $fragment = '';

    private static function getDnsNameRegex()
    {
        return '(?:'.self::REGEX_DNScomp.'(?:[.]'.self::REGEX_DNScomp.')*)';
    }

    private static function getTaggingEntityRegex()
    {
        return '(?<authorityName>'.self::getDnsNameRegex().'|'.static::getEmailAddressRegex().'),(?<date>'.self::REGEX_date.')';
    }

    private static function getEmailAddressRegex()
    {
        return '(?:[\w\d-._+]*@'.self::getDnsNameRegex().')';
    }

    private static function getDateRegex()
    {
        $day = self::REGEX_DATE_DAY;
        $month = self::REGEX_DATE_MONTH;
        $year = self::REGEX_DATE_YEAR;

        return "$year(?:-$month(?:-$day)?)?";
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return 'tag';
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        return $this->authorityName;
    }

    public function getAuthorityName()
    {
        return $this->getAuthority();
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        return '';
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost()
    {
        return $this->extractHost($this->getAuthority());
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        return null;
    }

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function getPath()
    {
        return '';
    }

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return '';
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Retrieve the specific component of the tag URI.
     *
     * If no specific is present, this method MUST return an empty string.
     *
     * The leading ":" character is not part of the specific and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc4151#section-2
     * @return string The tag URI specific.
     */
    public function getSpecific()
    {
        return $this->specific;
    }

    /**
     * Retrieve the date component of the tag URI.
     *
     * The leading "," character is not part of the date and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc4151#section-2
     * @return string The tag URI date.
     */
    public function getDate()
    {
        return $this->date;
    }

    public function getTaggingEntity()
    {
        return sprintf("%s,%s", $this->getAuthority(), $this->getDate());
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     * @return static A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme)
    {
        throw new \BadMethodCallException("This method is not supported");
    }

    /**
     * Return an instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string $user The user name to use for authority.
     * @param null|string $password The password associated with $user.
     * @return static A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        throw new \BadMethodCallException("This method is not supported");
    }

    /**
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host The hostname to use with the new instance.
     * @return static A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
        throw new \BadMethodCallException("This method is not supported");
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *     removes the port information.
     * @return static A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {
        throw new \BadMethodCallException("This method is not supported");
    }

    /**
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If the path is intended to be domain-relative rather than path relative then
     * it must begin with a slash ("/"). Paths not starting with a slash ("/")
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path The path to use with the new instance.
     * @return static A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        throw new \BadMethodCallException("This method is not supported");
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     * @return static A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        throw new \BadMethodCallException("This method is not supported");
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     * @return static A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        return (new TagUri())
            ->setAuthorityName($this->getAuthorityName())
            ->setDate($this->getDate())
            ->setSpecific($this->getSpecific())
            ->setFragment($fragment);
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString()
    {
        $tagURI = sprintf("tag:%s:%s", $this->getTaggingEntity(), $this->getSpecific());

        if ('' !== $fragment = $this->getFragment()) {
            $tagURI = sprintf("%s#%s", $tagURI, $fragment);
        }

        return $tagURI;
    }

    public function setAuthorityName($authorityName)
    {
        if (strstr($authorityName, '@') !== false) {
            $this->checkEmail($authorityName);
        }

        $this->authorityName = $authorityName;

        return $this;
    }

    public function setDate($date)
    {
        $dateRegex = self::getDateRegex();
        if (!preg_match("/^$dateRegex$/", $date, $m)) {
            throw new \InvalidArgumentException('Invalid date: '.$date);
        }

        $parts = array_merge([
            'year' => $m['year'],
            'month' => '01',
            'day' => '01',
        ], $m);

        if (!checkdate($parts['month'], $parts['day'], $parts['year'])) {
            throw new \InvalidArgumentException('Invalid date: '.$date);
        }
        $this->date = $date;

        return $this;
    }

    public function setSpecific($specific)
    {
        $this->specific = $specific;

        return $this;
    }

    public function setFragment($fragment)
    {
        $this->fragment = $fragment ?: '';

        return $this;
    }

    private function extractHost($authorityName)
    {
        $parts = explode('@', $authorityName);

        return end($parts);
    }

    public static function createFromString($string)
    {
        $overallRegex = self::REGEX_OVERALL;
        if (!preg_match("/{$overallRegex}/", $string, $overall)) {
            throw new \InvalidArgumentException("The provided tag URI doesn't seem to be valid: {$string}");
        }
        $overall = array_merge(['fragment' => ''], $overall);

        $taggingEntity = self::getTaggingEntityRegex();
        if (preg_match("/^{$taggingEntity}/", $overall['taggingEntity'], $m) !== 1) {
            throw new \InvalidArgumentException('Invalid taggingEntity: '.$overall['taggingEntity']);
        }

        $tagUri = (new TagUri())
            ->setAuthorityName($m['authorityName'])
            ->setDate($m['date'])
            ->setSpecific($overall['specific'])
            ->setFragment($overall['fragment']);

        return $tagUri;
    }

    private function checkEmail($email)
    {
        $validator = new EmailValidator();
        if (!$validator->isValid($email, new RFCValidation())) {
            throw new \InvalidArgumentException("Invalid authority name: '{$email}'. It doesn't seem to be a valid email address.");
        }

        return true;
    }
}
