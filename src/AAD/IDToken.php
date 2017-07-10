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

use microsoft\aadphp\AADPHPException;

/**
 * Azure AD specific IDToken implementation.
 */
class IDToken extends \microsoft\aadphp\OIDC\IDToken
{
    /**
     * Get a sensible username for the user represented by the idtoken.
     *
     * @return string A username for the user.
     */
    public function get_username()
    {
        $upn = $this->claim('upn');
        return (!empty($upn)) ? $upn : parent::get_username();
    }

    /**
     * Get a unique identifier for the user represented by the idtoken.
     *
     * @return string A unique identifier.
     */
    public function get_uniqid()
    {
        $oid = $this->claim('oid');
        return (!empty($oid)) ? $oid : parent::get_uniqid();
    }

    /**
     * Get current AAD signing keys.
     *
     * @param string $tenantid The tenant ID.
     * @param \microsoft\aadphp\HttpClientInterface $httpclient HTTP Client instance.
     * @return array Array of keys.
     */
    public static function get_keys(\microsoft\aadphp\HttpClientInterface $httpclient, $tenantid = null)
    {
        if (!empty($tenantid)) {
            $url = 'https://login.windows.net/' . $tenantid . '/.well-known/openid-configuration';
            $oidcconfig = $httpclient->get($url);
            $oidcconfig = @json_decode($oidcconfig, true);
            if (empty($oidcconfig) || !is_array($oidcconfig) || !isset($oidcconfig['jwks_uri'])) {
                throw new AADPHPException('Could not get openid connect config (1).');
            }
            $jwks_uri = $oidcconfig['jwks_uri'];
        } else {
            $jwks_uri = 'https://login.windows.net/common/discovery/keys';
        }

        $keydata = $httpclient->get($jwks_uri);
        $keydata = @json_decode($keydata, true);
        if (empty($keydata) || !is_array($keydata) || !isset($keydata['keys'])) {
            throw new AADPHPException('Could not get openid connect config (2).');
        }

        $keys = [];
        foreach ($keydata['keys'] as $i => $keyinfo) {
            if (isset($keyinfo['x5c']) && is_array($keyinfo['x5c'])) {
                foreach ($keyinfo['x5c'] as $encodedkey) {
                    $keys[] = static::transform_key($encodedkey);
                }
            }
        }
        return $keys;
    }

    /**
     * Transform encoded key into OpenSSL-compatible form.
     *
     * @param string $key Encoded key.
     * @return string OpenSSL-compatible key.
     */
    public static function transform_key($key)
    {
        $output = "-----BEGIN CERTIFICATE-----\n";
        $output .= wordwrap($key, 64, "\n", true);
        $output .= "\n-----END CERTIFICATE-----";
        return $output;
    }
}
