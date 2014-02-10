<?php

namespace PROCERGS\Generic\TwitterOAuthProxiedBundle\Service;

class TwitterOAuth extends \TwitterOAuth
{

    public static $proxySettings;

    public static function setProxy($proxySettings)
    {
        self::$proxySettings = $proxySettings;
    }

    /**
     * Make an HTTP request
     *
     * @return API results
     */
    function http($url, $method, $postfields = null, $files = array())
    {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, false);

        if (!empty(self::$proxySettings)) {
            $proxy = self::$proxySettings;
            curl_setopt($ci, CURLOPT_PROXYTYPE, $proxy['type']);
            curl_setopt($ci, CURLOPT_PROXY, $proxy['host']);
            curl_setopt($ci, CURLOPT_PROXYPORT, $proxy['port']);
            curl_setopt($ci, CURLOPT_PROXYUSERPWD, $proxy['auth']);
        }

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($files)) {
                    foreach ($files as $k => $v) {
                        if (0 !== strpos($v, '@')) {
                            $files[$k] = '@' . $v;
                        }
                    }
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $files);

                    if (!empty($postfields)) {
                        $url = $url . '?' . $postfields;
                    }
                } else if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }

        curl_setopt($ci, CURLOPT_URL, $url);
        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;
        curl_close($ci);
        return $response;
    }

}
