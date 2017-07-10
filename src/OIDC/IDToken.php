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

/**
 * IDToken implementation based on JWT.
 */
class IDToken extends \microsoft\aadphp\JWT implements \microsoft\aadphp\OIDC\IDTokenInterface
{
    /**
     * Get a sensible username for the user represented by the idtoken.
     *
     * @return string A username for the user.
     */
    public function get_username()
    {
        return $this->get_uniqid();
    }

    /**
     * Get a unique identifier for the user represented by the idtoken.
     *
     * @return string A unique identifier.
     */
    public function get_uniqid()
    {
        return $this->claim('sub');
    }

    /**
     * Get the nonce received in the idtoken.
     *
     * @return string A nonce.
     */
    public function get_nonce()
    {
        return $this->claim('nonce');
    }

    /**
     * Get the token's intended audience.
     *
     * @return string|array The intended audience.
     */
    public function get_audience()
    {
        return $this->claim('aud');
    }

    /**
     * Get token expiration time.
     *
     * @return int The expiration time.
     */
    public function get_expiration()
    {
        return (int)$this->claim('exp');
    }

    /**
     * Get an array of available user information.
     *
     * @return array Array of available user information.
     */
    public function get_userinfo()
    {
        $userinfo = [];

        $firstname = $this->claim('given_name');
        if (!empty($firstname)) {
            $userinfo['firstname'] = $firstname;
        }

        $lastname = $this->claim('family_name');
        if (!empty($lastname)) {
            $userinfo['lastname'] = $lastname;
        }

        $email = $this->claim('email');
        if (!empty($email)) {
            $userinfo['email'] = $email;
        }
        return $userinfo;
    }

    /**
     * Create an instance of the class from an encoded JWT string.
     *
     * @param string $encoded The encoded JWT.
     * @param array $keys Array of keys to verify JWT. At least one key must verify.
     * @return \microsoft\aadphp\JWT A JWT instance.
     */
    public static function instance_from_encoded($encoded, array $keys = array())
    {
        list($header, $body) = static::decode($encoded, $keys);
        $idtoken = new static;
        $idtoken->set_header($header);
        $idtoken->set_claims($body);
        $sub = $idtoken->claim('sub');
        if (empty($sub)) {
            throw new \Exception($this->lang['errorjwtmalformed']);
        }
        return $idtoken;
    }
}
