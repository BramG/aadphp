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

namespace microsoft\aadphp\AAD;

use \microsoft\aadphp\AADPHPException;

/**
 * Azure AD client.
 */
class Client extends \microsoft\aadphp\OIDC\Client
{
    /** @var string Auth endpoint. */
    protected $authendpoint = 'https://login.microsoftonline.com/common/oauth2/authorize';

    /** @var string Token endpoint. */
    protected $tokenendpoint = 'https://login.microsoftonline.com/common/oauth2/token';

    /** @var string The OIDC resource to use. */
    protected $resource = 'https://graph.windows.net';

    /** @var string To store AAD or hybrid flow. */
    protected $authflow = 'authorization';

    /**
     * Make a token request using the resource-owner credentials login flow.
     *
     * @param string $username The resource owner's username.
     * @param string $password The resource owner's password.
     * @return array Received parameters.
     */
    public function rocredsrequest($username, $password)
    {
        if (empty($this->tokenendpoint)) {
            throw new AADPHPException($this->lang['notokenendpoint']);
        }

        if (strpos($this->tokenendpoint, 'https://') !== 0) {
            throw new AADPHPException($this->lang['insecuretokenendpoint'], $this->tokenendpoint);
        }

        $params = [
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
            'scope' => 'openid profile email',
            'resource' => $this->resource,
            'client_id' => $this->clientid,
            'client_secret' => $this->clientsecret,
        ];

        $returned = $this->httpclient->post($this->tokenendpoint, $params);
        return $this->process_json_response($returned, ['token_type' => null, 'id_token' => null]);
    }

    /**
     * Construct an IDToken object from an encoded id_token string.
     *
     * @param string $idtoken An encoded id_token string.
     * @return \microsoft\aadphp\OIDC\IDTokenInterface An IDToken object.
     */
    protected function constructidtoken($idtoken)
    {
        $httpclient = new \microsoft\aadphp\HttpClient;
        $keys = IDToken::get_keys($httpclient);
        return \microsoft\aadphp\AAD\IDToken::instance_from_encoded($idtoken, $keys);
    }
}
