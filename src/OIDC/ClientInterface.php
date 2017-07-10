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
 * OpenID Connect Client Interface.
 */
interface ClientInterface
{
    /**
     * Constructor.
     *
     * @param \microsoft\aadphp\HttpClientInterface $httpclient An HTTP client to use for background communication.
     * @param \microsoft\aadphp\OIDC\StorageInterface $storage A storage implementation to use.
     */
    public function __construct(HttpClientInterface $httpclient, StorageInterface $storage);

    /**
     * Set auth endpoint.
     *
     * @param string $uri Authorization endpoint.
     */
    public function set_authendpoint($uri);

    /**
     * Set token endpoint.
     *
     * @param string $uri Token endpoint.
     */
    public function set_tokenendpoint($uri);

    /**
     * Set language strings.
     *
     * @param array $lang Array of language strings, using same keys as property $lang.
     */
    public function set_lang(array $lang);

    /**
     * Set the client ID.
     *
     * @param string $clientid The client ID to set.
     */
    public function set_clientid($clientid);

    /**
     * Set the client secret.
     *
     * @param string $clientsecret The client secret to set.
     */
    public function set_clientsecret($clientsecret);

    /**
     * Set the redirect URI.
     *
     * @param string $redirecturi The redirect URI to set.
     */
    public function set_redirecturi($redirecturi);

    /**
     * Set the resource.
     *
     * @param string $resource The redirect URI to set.
     */
    public function set_resource($resource);

    /**
     * Get the set client ID.
     *
     * @return string The set client ID.
     */
    public function get_clientid();

    /**
     * Get the set client secret.
     *
     * @return string The set client secret.
     */
    public function get_clientsecret();

    /**
     * Get the set redirect URI.
     *
     * @return string The set redirect URI.
     */
    public function get_redirecturi();

    /**
     * Get the set resource.
     *
     * @return string The set resource.
     */
    public function get_resource();

    /**
     * Get the set authendpoint.
     *
     * @return string The set endpoint URL.
     */
    public function get_authendpoint();

    /**
     * Get the set tokenendpoint.
     *
     * @return string The set endpoint URL.
     */
    public function get_tokenendpoint();

    /**
     * Perform an authorization request by redirecting resource owner's user agent to auth endpoint.
     *
     * @param bool $promptlogin Whether to prompt for login or use existing session.
     * @param array $stateparams Parameters to store as state.
     * @param array $extraparams Additional parameters to send with the OIDC request.
     */
    public function authrequest($promptlogin = false, array $stateparams = array(), array $extraparams = array());

    /**
     * Handle auth response.
     *
     * @param array $authparams Array of received auth response parameters.
     * @return array List of IDToken object, array of token parameters, and stored state parameters.
     */
    public function handle_auth_response(array $authparams);

    /**
     * Process and return idtoken.
     *
     * @param string $idtoken Encoded id token.
     * @param string $expectednonce Expected nonce to validate received nonce against.
     * @return \microsoft\aadphp\OIDC\IDTokenInterface An IDToken object.
     */
    public function process_idtoken($idtoken, $expectednonce = '');

    /**
     * Exchange an authorization code for an access token.
     *
     * @param string $code An authorization code.
     * @return array Received parameters.
     */
    public function tokenrequest($code);
}
