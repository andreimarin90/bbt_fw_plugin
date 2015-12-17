<?php
/**
 * The most popular PHP library for use with the Twitter OAuth REST API.
 *
 * @license MIT
 */
namespace Abraham\TwitterOAuth;

use Abraham\TwitterOAuth\Util\JsonDecoder;

/**
 * TwitterOAuth class for interacting with the Twitter API.
 *
 * @author Abraham Williams <abraham@abrah.am>
 */
class TwitterOAuth extends Config
{
    const API_VERSION = '1.1';
    const API_HOST = 'https://api.twitter.com';
    const UPLOAD_HOST = 'https://upload.twitter.com';
    const UPLOAD_CHUNK = 40960; // 1024 * 40

    /** @var Response details about the result of the last request */
    private $response;
    /** @var string|null Application bearer token */
    private $bearer;
    /** @var Consumer Twitter application details */
    private $consumer;
    /** @var Token|null User access token details */
    private $token;
    /** @var HmacSha1 OAuth 1 signature type used by Twitter */
    private $signatureMethod;

    /**
     * Constructor
     *
     * @param string      $consumerKey      The Application Consumer Key
     * @param string      $consumerSecret   The Application Consumer Secret
     * @param string|null $oauthToken       The Client Token (optional)
     * @param string|null $oauthTokenSecret The Client Token Secret (optional)
     */
    public function __construct($consumerKey, $consumerSecret, $oauthToken = null, $oauthTokenSecret = null)
    {
        $this->resetLastResponse();
        $this->signatureMethod = new HmacSha1();
        $this->consumer = new Consumer($consumerKey, $consumerSecret);
        if (!empty($oauthToken) && !empty($oauthTokenSecret)) {
            $this->token = new Token($oauthToken, $oauthTokenSecret);
        }
        if (empty($oauthToken) && !empty($oauthTokenSecret)) {
            $this->bearer = $oauthTokenSecret;
        }
    }

    /**
     * @param string $oauthToken
     * @param string $oauthTokenSecret
     */
    public function setOauthToken($oauthToken, $oauthTokenSecret)
    {
        $this->token = new Token($oauthToken, $oauthTokenSecret);
    }

    /**
     * @return string|null
     */
    public function getLastApiPath()
    {
        return $this->response->getApiPath();
    }

    /**
     * @return int
     */
    public function getLastHttpCode()
    {
        return $this->response->getHttpCode();
    }

    /**
     * @return array
     */
    public function getLastXHeaders()
    {
        return $this->response->getXHeaders();
    }

    /**
     * @return array|object|null
     */
    public function getLastBody()
    {
        return $this->response->getBody();
    }

    /**
     * Resets the last response cache.
     */
    public function resetLastResponse()
    {
        $this->response = new Response();
    }

    /**
     * Make URLs for user browser navigation.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    public function url($path, array $parameters)
    {
        $this->resetLastResponse();
        $this->response->setApiPath($path);
        $query = http_build_query($parameters);
        return sprintf('%s/%s?%s', self::API_HOST, $path, $query);
    }

    /**
     * Make /oauth/* requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array
     * @throws TwitterOAuthException
     */
    public function oauth($path, array $parameters = [])
    {
        $response = [];
        $this->resetLastResponse();
        $this->response->setApiPath($path);
        $url = sprintf('%s/%s', self::API_HOST, $path);
        $result = $this->oAuthRequest($url, 'POST', $parameters);

        if ($this->getLastHttpCode() != 200) {
            throw new TwitterOAuthException($result);
        }

        parse_str($result, $response);
        $this->response->setBody($response);

        return $response;
    }

    /**
     * Make /oauth2/* requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    public function oauth2($path, array $parameters = [])
    {
        $method = 'POST';
        $this->resetLastResponse();
        $this->response->setApiPath($path);
        $url = sprintf('%s/%s', self::API_HOST, $path);
        $request = Request::fromConsumerAndToken($this->consumer, $this->token, $method, $url, $parameters);
        $authorization = 'Authorization: Basic ' . $this->encodeAppAuthorization($this->consumer);
        $result = $this->request($request->getNormalizedHttpUrl(), $method, $authorization, $parameters);
        $response = JsonDecoder::decode($result, $this->decodeJsonAsArray);
        $this->response->setBody($response);
        return $response;
    }

    /**
     * Make GET requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    public function get($path, array $parameters = [])
    {
        return $this->http('GET', self::API_HOST, $path, $parameters);
    }

    /**
     * Make POST requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    public function post($path, array $parameters = [])
    {
        return $this->http('POST', self::API_HOST, $path, $parameters);
    }

    /**
     * Make DELETE requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    public function delete($path, array $parameters = [])
    {
        return $this->http('DELETE', self::API_HOST, $path, $parameters);
    }

    /**
     * Make PUT requests to the API.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    public function put($path, array $parameters = [])
    {
        return $this->http('PUT', self::API_HOST, $path, $parameters);
    }

    /**
     * @param string $method
     * @param string $host
     * @param string $path
     * @param array  $parameters
     *
     * @return array|object
     */
    private function http($method, $host, $path, array $parameters)
    {
        $this->resetLastResponse();
        $url = sprintf('%s/%s/%s.json', $host, self::API_VERSION, $path);
        $this->response->setApiPath($path);
        $result = $this->oAuthRequest($url, $method, $parameters);
        $response = JsonDecoder::decode($result, $this->decodeJsonAsArray);
        $this->response->setBody($response);
        return $response;
    }

    /**
     * Format and sign an OAuth / API request
     *
     * @param string $url
     * @param string $method
     * @param array  $parameters
     *
     * @return string
     * @throws TwitterOAuthException
     */
    private function oAuthRequest($url, $method, array $parameters)
    {
        $request = Request::fromConsumerAndToken($this->consumer, $this->token, $method, $url, $parameters);
        if (array_key_exists('oauth_callback', $parameters)) {
            // Twitter doesn't like oauth_callback as a parameter.
            unset($parameters['oauth_callback']);
        }
        if ($this->bearer === null) {
            $request->signRequest($this->signatureMethod, $this->consumer, $this->token);
            $authorization = $request->toHeader();
        } else {
            $authorization = 'Authorization: Bearer ' . $this->bearer;
        }
        return $this->request($request->getNormalizedHttpUrl(), $method, $authorization, $parameters);
    }

    /**
     * Make an HTTP request
     *
     * @param string $url
     * @param string $method
     * @param string $authorization
     * @param array $postfields
     *
     * @return string
     * @throws TwitterOAuthException
     */
    private function request($url, $method, $authorization, $postfields)
    {
        /* Changed to WP function wp_remote_get */
        $args = array(
            'timeout'     => $this->timeout,
            'user-agent'  => $this->userAgent,
            'headers'     => $authorization,
            'compress'    => 'gzip',
        );
        if (in_array($method, ['GET', 'PUT', 'DELETE']) && !empty($postfields)) {
            $url .= '?' . Util::buildHttpQuery($postfields);
        }

        $response_raw = wp_remote_get( $url, $args );
        $response = wp_remote_retrieve_body( $response_raw );

        if(!empty($response_raw['response']['code']))
            $this->response->setHttpCode($response_raw['response']['code']);
        $parts = explode("\r\n\r\n", $response);
        $responseBody = array_pop($parts);
        $responseHeader = array_pop($parts);
        $this->response->setHeaders($this->parseHeaders($responseHeader));


        return $responseBody;
    }

    /**
     * Get the header info to store.
     *
     * @param string $header
     *
     * @return array
     */
    private function parseHeaders($header)
    {
        $headers = [];
        foreach (explode("\r\n", $header) as $line) {
            if (strpos($line, ':') !== false) {
                list ($key, $value) = explode(': ', $line);
                $key = str_replace('-', '_', strtolower($key));
                $headers[$key] = trim($value);
            }
        }
        return $headers;
    }

    /**
     * Encode application authorization header.
     *
     * @param Consumer $consumer
     *
     * @return string
     */
    private function encodeAppAuthorization($consumer)
    {
        // TODO: key and secret should be rfc 1738 encoded
        $key = $consumer->key;
        $secret = $consumer->secret;
        return base64_encode($key . ':' . $secret);
    }
}