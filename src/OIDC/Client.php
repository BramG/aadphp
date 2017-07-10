<?php
/**
 * Copyright (c) 2016 Micorosft Corporation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author James McQuillan <james.mcquillan@remote-learner.net>
 * @license MIT
 * @copyright (C) 2016 onwards Microsoft Corporation (http://microsoft.com/)
 */

namespace microsoft\aadphp\OIDC;

use \microsoft\aadphp\HttpClientInterface;
use \microsoft\aadphp\AADPHPException;

/**
 * OpenID Connect Client.
 */
class Client implements \microsoft\aadphp\OIDC\ClientInterface
{
    /** @var \microsoft\aadphp\HttpClientInterface An HTTP client to use. */
    protected $httpclient;

    /** @var \microsoft\aadphp\OIDC\StorageInterface A storage implementation to use. */
    protected $storage;

    /** @var string The client ID. */
    protected $clientid;

    /** @var string The client secret. */
    protected $clientsecret;

    /** @var string The client redirect URI. */
    protected $redirecturi;

    /** @var string The OIDC resource to use. */
    protected $resource;

    /** @var string Auth endpoint. */
    protected $authendpoint = '';

    /** @var string Token endpoint. */
    protected $tokenendpoint = '';

    /** @var string To store AAD or hybrid flow. */
    protected $authflow = '';

    /** @var array of supported auth flows. */
    protected $supportedauthflows = array("authorization", "hybrid", "rocred");

    /** @var array Array of language strings. */
    protected $lang = [
        'invalidendpoint' => 'Invalid Endpoint URI received.',
        'nocreds' => 'No OpenID credentials set, please set.',
        'noauthendpoint' => 'No authorization endpoint set, please set with set_authendpoint().',
        'notokenendpoint' => 'No token endpoint set, please set with set_tokenendpoint().',
        'insecureauthendpoint' => 'Insecure authorization endpoint set. Authorization endpoint must start with https://',
        'insecuretokenendpoint' => 'Insecure token endpoint set. Token endpoint must start with https://',
        'errorresponse' => 'Error response received.',
        'invalidresponse' => 'Invalid response received.',
        'invalidresponse_keymissing' => 'Invalid response received, missing parameter.',
        'invalidresponse_badvalue' => 'Invalid response received, unexpected value encountered.',
        'noauthcode' => 'No auth code received.',
        'unknownstate' => 'Unknown state.',
        'noidtoken' => 'No ID Token received.',
        'invalididtoken' => 'Invalid ID token received.',
        'invalididtoken_nonce' => 'Invalid ID token: bad nonce encountered.',
        'invalididtoken_aud' => 'Invalid ID token: invalid audience.',
        'invalididtoken_exp' => 'Invalid ID token: token expired.',
    ];

    /**
     * Constructor.
     *
     * @param \microsoft\aadphp\HttpClientInterface $httpclient An HTTP client to use for background communication.
     * @param \microsoft\aadphp\OIDC\StorageInterface $storage A storage implementation to use.
     */
    public function __construct(HttpClientInterface $httpclient, StorageInterface $storage)
    {
        $this->httpclient = $httpclient;
        $this->storage = $storage;
    }

    /**
     * Set auth endpoint.
     *
     * @param string $uri Authorization endpoint.
     */
    public function set_authendpoint($uri)
    {
        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new AADPHPException($this->lang['invalidendpoint'], $uri);
        }
        $this->authendpoint = $uri;
    }

    /**
     * Set token endpoint.
     *
     * @param string $uri Token endpoint.
     */
    public function set_tokenendpoint($uri)
    {
        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new AADPHPException($this->lang['invalidendpoint'], $uri);
        }
        $this->tokenendpoint = $uri;
    }

    /**
     * Set language strings.
     *
     * @param array $lang Array of language strings, using same keys as property $lang.
     */
    public function set_lang(array $lang)
    {
        foreach ($lang as $k => $v) {
            if (isset($this->lang[$k])) {
                $this->lang[$k] = $v;
            }
        }
    }

    /**
     * Set the client ID.
     *
     * @param string $clientid The client ID to set.
     */
    public function set_clientid($clientid)
    {
        $this->clientid = $clientid;
    }

    /**
     * Set the client secret.
     *
     * @param string $clientsecret The client secret to set.
     */
    public function set_clientsecret($clientsecret)
    {
        $this->clientsecret = $clientsecret;
    }

    /**
     * Set the redirect URI.
     *
     * @param string $redirecturi The redirect URI to set.
     */
    public function set_redirecturi($redirecturi)
    {
        $this->redirecturi = $redirecturi;
    }

    /**
     * Set the resource.
     *
     * @param string $resource The redirect URI to set.
     */
    public function set_resource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Set the authflow.
     *
     * @param string $authflow The authflow to set.
     */
    public function set_authflow($authflow)
    {

        if (in_array($authflow, $this->supportedauthflows)) {
            $this->authflow = $authflow;
        } else {
            throw new AADPHPException('Invalid authflow.');
        }

    }

    /**
     * Get the authflow.
     *
     * @return string $authflow The authflow to get.
     */
    public function get_authflow()
    {
        return $this->authflow;
    }

    /**
     * Get the set client ID.
     *
     * @return string The set client ID.
     */
    public function get_clientid()
    {
        return (isset($this->clientid)) ? $this->clientid : null;
    }

    /**
     * Get the set client secret.
     *
     * @return string The set client secret.
     */
    public function get_clientsecret()
    {
        return (isset($this->clientsecret)) ? $this->clientsecret : null;
    }

    /**
     * Get the set redirect URI.
     *
     * @return string The set redirect URI.
     */
    public function get_redirecturi()
    {
        return (isset($this->redirecturi)) ? $this->redirecturi : null;
    }

    /**
     * Get the set resource.
     *
     * @return string The set resource.
     */
    public function get_resource()
    {
        return (isset($this->resource)) ? $this->resource : null;
    }

    /**
     * Get the set authendpoint.
     *
     * @return string The set endpoint URL.
     */
    public function get_authendpoint()
    {
        return $this->authendpoint;
    }

    /**
     * Get the set tokenendpoint.
     *
     * @return string The set endpoint URL.
     */
    public function get_tokenendpoint()
    {
        return $this->tokenendpoint;
    }

    /**
     * Get an array of authorization request parameters.
     *
     * @param array $stateparams Parameters to store as state.
     * @param array $extraparams Additional parameters to send with the OIDC request.
     * @return array Array of request parameters.
     */
    protected function getauthrequestparams(array $stateparams = array(), array $extraparams = array())
    {
        $nonce = str_replace('.', '', uniqid('', true));
        $nonce .= $this->get_random_string(41);

        $params = [
            'scope' => 'openid',
            'response_type' => 'code',
            'client_id' => $this->clientid,
            'redirect_uri' => $this->redirecturi,
            'state' => $this->getnewstate($nonce, $stateparams),
            'response_mode' => 'form_post',
            'nonce' => $nonce,
            'resource' => $this->resource,
        ];

        if ($this->authflow == 'hybrid') {
            $params['response_type'] = 'code id_token';
        }

        $params = array_merge($params, $extraparams);
        return $params;
    }

    /**
     * Get a random string.
     *
     * @param int $length The length to generate.
     * @return string The random string.
     */
    protected function get_random_string($length)
    {
        $output = '';
        $chrs = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numc = strlen($chrs) - 1;
        for ($i = 0; $i < $length; $i++) {
            $output .= $chrs[mt_rand(0, $numc)];
        }
        return $output;
    }

    /**
     * Generate a new state parameter.
     *
     * @param string $nonce The generated nonce value.
     * @param array $stateparams Additional data to be stored with the state.
     * @return string The new state value.
     */
    protected function getnewstate($nonce, array $stateparams = array())
    {
        $state = $this->get_random_string(15);
        $this->storage->store_state($state, $nonce, $stateparams);
        return $state;
    }

    /**
     * Perform an authorization request by redirecting resource owner's user agent to auth endpoint.
     *
     * @param bool $promptlogin Whether to prompt for login or use existing session.
     * @param array $stateparams Parameters to store as state.
     * @param array $extraparams Additional parameters to send with the OIDC request.
     */
    public function authrequest($promptlogin = false, array $stateparams = array(), array $extraparams = array())
    {
        if (empty($this->clientid)) {
            throw new AADPHPException($this->lang['nocreds']);
        }

        if (empty($this->authendpoint)) {
            throw new AADPHPException($this->lang['noauthendpoint']);
        }

        if (strpos($this->authendpoint, 'https://') !== 0) {
            throw new AADPHPException($this->lang['insecureauthendpoint'], $this->authendpoint);
        }

        if ($promptlogin === true) {
            $extraparams['prompt'] = 'login';
        }
        $params = $this->getauthrequestparams($stateparams, $extraparams);
        $params = http_build_query($params, '', '&');
        $url = $this->authendpoint;
        $url = (strpos($url, '?') !== false) ? $url . '&' . $params : $url . '?' . $params;
        header('Location: ' . $url);
        die();
    }

    /**
     * Handle auth response.
     *
     * @param array $authparams Array of received auth response parameters.
     * @return array List of IDToken object, array of token parameters, and stored state parameters.
     */
    public function handle_auth_response(array $authparams)
    {
        // Validate response.
        if (!isset($authparams['code'])) {
            throw new AADPHPException($this->lang['noauthcode'], $authparams);
        }
        if (!isset($authparams['state'])) {
            throw new AADPHPException($this->lang['unknownstate'], $authparams);
        }

        // Look up state.
        list($stateparams, $nonce) = $this->storage->get_state($authparams['state']);

        // Expire state record.
        $this->storage->delete_state($nonce);

        // Exchange auth code for token.
        $tokenparams = $this->tokenrequest($authparams['code']);

        // Process id_token.
        if (!isset($tokenparams['id_token'])) {
            throw new AADPHPException($this->lang['noidtoken'], $tokenparams);
        }

        $idtoken = $this->process_idtoken($tokenparams['id_token'], $nonce);

        return [$idtoken, $tokenparams, $stateparams];
    }

    /**
     * Process and return idtoken.
     *
     * @param string $idtoken Encoded id token.
     * @param string $expectednonce Expected nonce to validate received nonce against.
     * @return \microsoft\aadphp\OIDC\IDTokenInterface An IDToken object.
     */
    public function process_idtoken($idtoken, $expectednonce = '')
    {
        // Decode id_token.
        try {
            $idtoken = $this->constructidtoken($idtoken);
        } catch (\Exception $e) {
            $errmsg = $this->lang['invalididtoken'] . ': ' . $e->getMessage();
            throw new AADPHPException($errmsg, $idtoken);
        }

        // Validate id_token nonce.
        $receivednonce = $idtoken->get_nonce();
        if (!empty($expectednonce) && (empty($receivednonce) || $receivednonce !== $expectednonce)) {
            $debugparams = ['idtoken' => $idtoken, 'expectednonce' => $expectednonce];
            throw new AADPHPException($this->lang['invalididtoken_nonce'], $debugparams);
        }

        // Validate audience.
        $aud = $idtoken->get_audience();
        if (is_array($aud)) {
            $found = false;
            foreach ($aud as $audval) {
                if ($audval === $this->clientid) {
                    $found = true;
                }
            }
            if ($found !== true) {
                $debugparams = ['clientid' => $this->clientid, 'aud' => $aud];
                throw new AADPHPException($this->lang['invalididtoken_aud'], $debugparams);
            }
        } elseif (!is_string($aud) || $aud !== $this->clientid) {
            $debugparams = ['clientid' => $this->clientid, 'aud' => $aud];
            throw new AADPHPException($this->lang['invalididtoken_aud'], $debugparams);
        }

        // Validate expiration.
        $exp = $idtoken->get_expiration();
        $now = time();
        if ($exp <= $now) {
            $debugparams = ['exp' => $exp, 'now' => $now];
            throw new AADPHPException($this->lang['invalididtoken_exp'], $debugparams);
        }

        return $idtoken;
    }

    /**
     * Construct an IDToken object from an encoded id_token string.
     *
     * @param string $idtoken An encoded id_token string.
     * @return \microsoft\aadphp\OIDC\IDTokenInterface An IDToken object.
     */
    protected function constructidtoken($idtoken)
    {
        return IDToken::instance_from_encoded($idtoken, $key);
    }

    /**
     * Process an OIDC JSON response.
     *
     * @throws Exception If an error response is received or if the response doesn't match the supplied expected structure.
     * @param string $response JSON-encoded OIDC response.
     * @param array $expectedstructure Array defining the expected parameters of the response.
     * @return array Decoded and validated response.
     */
    protected function process_json_response($response, array $expectedstructure = array())
    {
        $result = @json_decode($response, true);
        if (empty($result) || !is_array($result)) {
            throw new AADPHPException($this->lang['invalidresponse'], $response);
        }

        if (isset($result['error'])) {
            $errormsg = $this->lang['errorresponse'];
            if (isset($result['error_description'])) {
                $errormsg .= ': ' . $result['error_description'];
            }
            throw new AADPHPException($errormsg, $response);
        }

        if (!empty($expectedstructure)) {
            $debugparams = ['received' => $response, 'expected' => $expectedstructure];
            foreach ($expectedstructure as $key => $val) {
                if (!isset($result[$key])) {
                    $debugparams['missingkey'] = $key;
                    $errormsg = $this->lang['invalidresponse_keymissing'];
                    throw new AADPHPException($errormsg, $debugparams);
                }

                if ($val !== null && $result[$key] !== $val) {
                    $debugparams['badvalue'] = [
                        'key' => $key,
                        'received' => $this->tostring($result[$key]),
                        'expected' => $this->tostring($val),
                    ];
                    $errormsg = $this->lang['invalidresponse_badvalue'];
                    throw new AADPHPException($errormsg, $debugparams);
                }
            }
        }

        return $result;
    }

    /**
     * Convert any value into a debuggable string.
     *
     * @param mixed $val The variable to convert.
     * @return string A string representation.
     */
    protected function tostring($val)
    {
        if (is_scalar($val)) {
            if (is_bool($val)) {
                return '(bool)' . (string)(int)$val;
            } else {
                return '(' . gettype($val) . ')' . (string)$val;
            }
        } else if (is_null($val)) {
            return '(null)';
        } else {
            return print_r($val, true);
        }
    }

    /**
     * Exchange an authorization code for an access token.
     *
     * @param string $code An authorization code.
     * @return array Received parameters.
     */
    public function tokenrequest($code)
    {
        if (empty($this->tokenendpoint)) {
            throw new AADPHPException($this->lang['notokenendpoint']);
        }

        if (strpos($this->tokenendpoint, 'https://') !== 0) {
            throw new AADPHPException($this->lang['insecuretokenendpoint'], $this->tokenendpoint);
        }

        $params = [
            'client_id' => $this->clientid,
            'client_secret' => $this->clientsecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirecturi,
        ];

        $returned = $this->httpclient->post($this->tokenendpoint, $params);
        return $this->process_json_response($returned, ['id_token' => null]);
    }

    /**
     * Handle auth response.
     *
     * @param array $authparams Array of received auth response parameters.
     * @return array List of IDToken object and stored state parameters.
     */
    public function handle_id_token(array $authparams)
    {
        // Validate response.
        if (!isset($authparams['state'])) {
            throw new AADPHPException($this->lang['unknownstate'], $authparams);
        }

        // Look up state.
        list($stateparams, $nonce) = $this->storage->get_state($authparams['state']);

        // Expire state record.
        $this->storage->delete_state($nonce);

        $idtoken = $this->process_idtoken($authparams['id_token'], $nonce);

        return [$idtoken, $stateparams];
    }
}
