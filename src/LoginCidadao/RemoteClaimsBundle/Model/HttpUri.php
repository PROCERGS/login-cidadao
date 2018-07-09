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

use Psr\Http\Message\UriInterface;

class HttpUri implements UriInterface
{
    /**
     * Pattern extracted from https://regex101.com/r/BGxJ6n/1/codegen?language=php
     *
     * TODO: use the pattern from LoginCidadao\ValidationBundle\Validator\Constraints\UriValidator when it's merged
     */
    const RFC3986 = '/(?#URI)^(?#
    Scheme  )(?<scheme>https|http):(?#
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

    /** @var string */
    private $scheme = '';

    /** @var string */
    private $userInfo = '';

    /** @var string */
    private $host = '';

    /** @var null|int */
    private $port = null;

    /** @var string */
    private $path = '';

    /** @var string */
    private $query = '';

    /** @var string */
    private $fragment = '';

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
        return $this->scheme;
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
        $userInfoHost = implode('@', array_filter([$this->getUserInfo(), $this->getHost()]));

        return implode(':', array_filter([$userInfoHost, $this->getPort()]));
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
        return $this->userInfo;
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
        return $this->host;
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
        return $this->port;
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
        return $this->path;
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
        return $this->query;
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
        return $this->with('scheme', $scheme);
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
        $userInfo = implode(':', [$user, $password]);

        return $this->with('userInfo', $userInfo);
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
        return $this->with('host', $host);
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
        return $this->with('port', $port);
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
        return $this->with('path', $path);
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
        return $this->with('query', $query);
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
        return $this->with('fragment', $fragment);
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
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = $this->getPath();
        $fragment = $this->getFragment() ? '#'.$this->getFragment() : '';
        $query = $this->getQuery() ? '?'.$this->getQuery() : '';

        return "{$scheme}://{$authority}{$path}{$query}{$fragment}";
    }

    /**
     * @param string $scheme
     * @return HttpUri
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * @param string $userInfo
     * @return HttpUri
     */
    public function setUserInfo($userInfo)
    {
        $this->userInfo = $userInfo;

        return $this;
    }

    /**
     * @param string $host
     * @return HttpUri
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @param int|null $port
     * @return HttpUri
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @param string $path
     * @return HttpUri
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param string $query
     * @return HttpUri
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param string $fragment
     * @return HttpUri
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;

        return $this;
    }

    private static function regexDecomposeUri($uri)
    {
        if (!preg_match(self::RFC3986, $uri, $m)) {
            throw new \InvalidArgumentException("Invalid HTTP URI: {$uri}");
        }

        foreach ($m as $key => $value) {
            if (is_int($key)) {
                unset($m[$key]);
            }
        }

        return $m;
    }

    private static function sanitizeComponents($components)
    {
        $components['userInfo'] = preg_replace('/[@]$/', '', $components['userInfo']);
        $components['query'] = preg_replace('/^[?]/', '', $components['query']);
        $components['fragment'] = preg_replace('/^[#]/', '', $components['fragment']);
        $components['port'] = str_replace(':', '', $components['port']);
        if (!is_numeric($components['port'])) {
            $components['port'] = null;
        }

        return $components;
    }

    public static function parseUri($uri)
    {
        $components = self::getDefaultComponents();
        $allowedComponents = ['scheme', 'userInfo', 'host', 'port', 'path', 'query', 'fragment'];

        foreach (self::regexDecomposeUri($uri) as $component => $value) {
            if (array_search($component, $allowedComponents) !== false) {
                $components[$component] = $value;
            }
        }

        return self::sanitizeComponents($components);
    }

    public static function createFromString($uri)
    {
        $parts = self::parseUri($uri);

        return self::createFromComponents($parts);
    }

    public static function createFromComponents($parts)
    {
        // Set default values
        $parts = array_merge(self::getDefaultComponents(), $parts);

        $uri = (new HttpUri())
            ->setScheme($parts['scheme'])
            ->setUserInfo($parts['userInfo'])
            ->setHost($parts['host'])
            ->setPort($parts['port'])
            ->setPath($parts['path'])
            ->setQuery($parts['query'])
            ->setFragment($parts['fragment']);

        return $uri;
    }

    private static function getDefaultComponents()
    {
        return [
            'scheme' => '',
            'userInfo' => '',
            'host' => '',
            'port' => null,
            'path' => '',
            'query' => '',
            'fragment' => '',
        ];
    }

    private function getComponents()
    {
        return [
            'scheme' => $this->getScheme(),
            'userInfo' => $this->getUserInfo(),
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'path' => $this->getPath(),
            'query' => $this->getQuery(),
            'fragment' => $this->getFragment(),
        ];
    }

    private function with($component, $value)
    {
        $components = $this->getComponents();
        $components[$component] = $value;

        return self::createFromComponents($components);
    }
}
