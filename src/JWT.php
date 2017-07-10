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

namespace microsoft\aadphp;

use microsoft\aadphp\AADPHPException;

/**
 * Class for working with JWTs.
 */
class JWT
{

    /** @var array Array of JWT header parameters. */
    protected $header = [];

    /** @var array Array of JWT claims. */
    protected $claims = [];

    /** @var array Array of language strings. */
    protected static $lang = [
        'errorjwtempty' => 'Empty or non-string JWT received.',
        'errorjwtmalformed' => 'Malformed JWT received.',
        'errorjwtcouldnotreadheader' => 'Could not read JWT header.',
        'errorjwtinvalidheader' => 'Invalid JWT header',
        'errorjwtunsupportedalg' => 'JWS Alg or JWE not supported',
        'errorjwtbadpayload' => 'Could not read JWT payload.',
    ];

    /**
     * Constructor.
     *
     * @param string $encoded An encoded JWT.
     */
    public function __construct($encoded = '')
    {
        if (!empty($encoded)) {
            list($header, $body) = $this->decode($encoded);
            $this->set_header($header);
            $this->set_claims($body);
        }
    }

    /**
     * Set language strings.
     *
     * @param array $lang Array of language strings, in same form as static property $lang.
     */
    public static function set_lang(array $lang)
    {
        foreach ($lang as $k => $v) {
            if (isset(static::$lang[$k])) {
                static::$lang[$k] = $v;
            }
        }
    }

    /**
     * Decode an encoded JWT.
     *
     * @param string $encoded Encoded JWT.
     * @param array $keys Array of keys to verify JWT. At least one key must verify.
     * @return array Array of arrays of header and body parameters.
     */
    protected static function decode($encoded, array $keys = array())
    {
        if (empty($encoded) || !is_string($encoded)) {
            throw new AADPHPException(static::$lang['errorjwtempty']);
        }
        $jwtparts = explode('.', $encoded);
        if (count($jwtparts) !== 3) {
            throw new AADPHPException(static::$lang['errorjwtmalformed']);
        }

        $header = static::decode_part($jwtparts[0]);
        if (empty($header)) {
            throw new AADPHPException(static::$lang['errorjwtcouldnotreadheader']);
        }
        if (!isset($header['alg'])) {
            throw new AADPHPException(static::$lang['errorjwtinvalidheader']);
        }

        static::verify($jwtparts[0] . '.' . $jwtparts[1], $jwtparts[2], $keys, $header['alg']);

        $body = static::decode_part($jwtparts[1]);
        if (empty($body)) {
            throw new AADPHPException(static::$lang['errorjwtbadpayload']);
        }

        return [$header, $body];
    }

    /**
     * Verify the JWT.
     *
     * @throws AADPHPException If verification fails.
     * @param string $payload Encoded JWT payload.
     * @param string $signature Encoded signature.
     * @param string $keys Public signing keys.
     * @param string $alg Verification algorithm.
     * @return bool True if successful.
     */
    protected static function verify($payload, $signature, $keys, $alg)
    {
        $signature = static::urlsafebase64decode($signature);
        if (!empty($keys) && trim($signature) === '') {
            throw new AADPHPException('Required JWT signature not received.');
        }

        switch ($alg) {
            case 'none':
                if (!empty($keys)) {
                    throw new AADPHPException('Unsigned JWT received when key was provided.');
                }
                return true;

            case 'RS256':
                if (empty($keys)) {
                    throw new AADPHPException('Key required for signed JWT.');
                }
                $verified = false;
                foreach ($keys as $key) {
                    $success = openssl_verify($payload, $signature, $key, OPENSSL_ALGO_SHA256);
                    if ($success === 1) {
                        $verified = true;
                    }
                }
                if ($verified === false) {
                    throw new AADPHPException('JWT signature not verified.');
                }
                return true;

            default:
                throw new AADPHPException(static::$lang['errorjwtunsupportedalg'], $alg);
        }
    }

    /**
     * URL-safe base64_decode
     *
     * @param string $input Input text.
     * @return string Output text.
     */
    public static function urlsafebase64decode($input)
    {
        $output = strtr($input, '-_', '+/');
        $output = base64_decode($output);
        return $output;
    }

    /**
     * Decode a part of a JWT.
     *
     * @param string $part An encoded JWT part.
     * @return array|null Decoded part, or null if invalid.
     */
    public static function decode_part($part)
    {
        $decoded = static::urlsafebase64decode($part);
        if (!empty($decoded)) {
            $decoded = @json_decode($decoded, true);
        }
        return (!empty($decoded) && is_array($decoded)) ? $decoded : null;
    }

    /**
     * Create an instance of the class from an encoded JWT string.
     *
     * @param string $encoded The encoded JWT.
     * @return \microsoft\aadphp\JWT A JWT instance.
     */
    public static function instance_from_encoded($encoded)
    {
        list($header, $body) = static::decode($encoded);
        $jwt = new static;
        $jwt->set_header($header);
        $jwt->set_claims($body);
        return $jwt;
    }

    /**
     * Set the JWT header.
     *
     * @param array $params The header params to set. Note, this will overwrite the existing header completely.
     */
    public function set_header(array $params)
    {
        $this->header = $params;
    }

    /**
     * Set claims in the object.
     *
     * @param array $params An array of claims to set. This will be appended to existing claims. Claims with the same keys will be
     *                      overwritten.
     */
    public function set_claims(array $params)
    {
        $this->claims = array_merge($this->claims, $params);
    }

    /**
     * Get the value of a claim.
     *
     * @param string $claim The name of the claim to get.
     * @return mixed The value of the claim.
     */
    public function claim($claim)
    {
        return (isset($this->claims[$claim])) ? $this->claims[$claim] : null;
    }
}
