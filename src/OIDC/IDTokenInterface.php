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
 * Interface defining an IDToken.
 */
interface IDTokenInterface
{
    /**
     * Get a sensible username for the user represented by the idtoken.
     *
     * @return string A username for the user.
     */
    public function get_username();

    /**
     * Get a unique identifier for the user represented by the idtoken.
     *
     * @return string A unique identifier.
     */
    public function get_uniqid();

    /**
     * Get the nonce received in the idtoken.
     *
     * @return string A nonce.
     */
    public function get_nonce();

    /**
     * Get the token's intended audience.
     *
     * @return string|array The intended audience.
     */
    public function get_audience();

    /**
     * Get token expiration time.
     *
     * @return int The expiration time.
     */
    public function get_expiration();

    /**
     * Get an array of available user information.
     *
     * @return array Array of available user information.
     */
    public function get_userinfo();

    /**
     * Construct an IDToken object from an encoded string.
     *
     * @param string $encoded An encoded idtoken.
     * @return \microsoft\aadphp\OIDC\IDTokenInterface A constructed IDToken object.
     */
    public static function instance_from_encoded($encoded);
}
