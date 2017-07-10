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
 * @author Nagesh Tembhurnikar <nagesh@introp.net>
 * @license MIT
 * @copyright (C) 2016 onwards Microsoft Corporation (http://microsoft.com/)
 */

namespace microsoft\aadphp\AAD;

/**
 * Represents an oauth2 token.
 */
class token
{
    /** @var string The access token. */
    protected $token;

    /** @var int The timestamp of when the token expires. */
    protected $expiry;

    /** @var string The refresh token. */
    protected $refreshtoken;

    /** @var string The token's scope. */
    protected $scope;

    /** @var string The token's resource. */
    protected $resource;

    /** @var \microsoft\aadphp\AAD\Client Client data used for refreshing the token if needed. */
    protected $clientdata;

    /** @var \microsoft\aadphp\HttpClientInterface An HTTP client used for refreshing the token if needed. */
    protected $httpclient;

    /**
     * Constructor.
     *
     * @param string $token The access token.
     * @param int $expiry The timestamp of when the token expires.
     * @param string $refreshtoken The refresh token.
     * @param string $scope The token's scope.
     * @param string $resource The token's resource.
     * @param \microsoft\aadphp\AAD\Client $clientdata Client data used for refreshing the token if needed.
     * @param \microsoft\aadphp\HttpClientInterface $httpclient An HTTP client used for refreshing the token if needed.
     */
    public function __construct(
        $token,
        $expiry,
        $refreshtoken,
        $scope,
        $resource,
        \microsoft\aadphp\AAD\Client $clientdata,
        \microsoft\aadphp\HttpClientInterface $httpclient
    ) {
        $this->clientdata = $clientdata;
        $this->httpclient = $httpclient;
        $this->token = $token;
        $this->expiry = $expiry;
        $this->refreshtoken = $refreshtoken;
        $this->scope = $scope;
        $this->resource = $resource;
    }

    /**
     * Get the access token.
     *
     * @return string $token The access token.
     */
    public function get_token()
    {
        return $this->token;
    }

    /**
     * Get the timestamp of when the token expires.
     *
     * @return int $expiry The timestamp of when the token expires.
     */
    public function get_expiry()
    {
        return $this->expiry;
    }

    /**
     * Get the refresh token.
     *
     * @return string $refreshtoken The refresh token.
     */
    public function get_refreshtoken()
    {
        return $this->refreshtoken;
    }

    /**
     * Get the token's scope.
     *
     * @return string $scope The token's scope.
     */
    public function get_scope()
    {
        return $this->scope;
    }

    /**
     * Get the token's resource.
     *
     * @return string The token's resource.
     */
    public function get_resource()
    {
        return $this->resource;
    }

    /**
     * Determine whether the token is expired.
     *
     * @return bool Whether the token is expired.
     */
    public function is_expired()
    {
        return ($this->expiry <= time()) ? true : false;
    }

    /**
     * Refresh the token.
     *
     * @return bool Success/Failure.
     */
    public function refresh()
    {
        $result = '';
        if (!empty($this->refreshtoken)) {

            $params = [
                'client_id' => $this->clientdata->get_clientid(),
                'client_secret' => $this->clientdata->get_clientsecret(),
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refreshtoken,
                'redirect_uri' => $this->clientdata->get_redirecturi(),
                'resource' => $this->clientdata->get_resource()
            ];
            $params = http_build_query($params, '', '&');
            $tokenendpoint = $this->clientdata->get_tokenendpoint();

            try {
                $result = $this->httpclient->post($tokenendpoint, $params);
            } catch (\Exception $e) {
                return false;
            }
            $result = json_decode($result, true);
        }
        if (!empty($result) && is_array($result) && isset($result['access_token'])) {

            $this->token = $result['access_token'];
            $this->expiry = $result['expires_on'];
            $this->refreshtoken = $result['refresh_token'];
            $this->scope = $result['scope'];
            $this->resource = $result['resource'];
            return $result;
        } else {
            return false;
        }
    }
}
