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

namespace microsoft\aadphp\tests;

/**
 * @codeCoverageIgnore
 */
class MockJWT extends \microsoft\aadphp\JWT
{
    /**
     * Decode an encoded JWT.
     *
     * @param string $encoded Encoded JWT.
     * @return array Array of arrays of header and body parameters.
     */
    public static function decode($encoded)
    {
        return parent::decode($encoded);
    }
}

/**
 * Tests JWT
 *
 * @group aadphp
 * @codeCoverageIgnore
 */
class JWTTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Dataprovider for test_decode.
     *
     * @return array Array of arrays of test parameters.
     */
    public function dataprovider_decode()
    {
        $tests = [];

        $tests['emptytest'] = [
            '',
            '',
            ['Exception', 'Empty or non-string JWT received.']
        ];

        $tests['nonstringtest'] = [
            100,
            '',
            ['Exception', 'Empty or non-string JWT received.']
        ];

        $tests['malformed1'] = [
            'a',
            '',
            ['Exception', 'Malformed JWT received.']
        ];

        $tests['malformed2'] = [
            'a.b',
            '',
            ['Exception', 'Malformed JWT received.']
        ];

        $tests['malformed3'] = [
            'a.b.c.d',
            '',
            ['Exception', 'Malformed JWT received.']
        ];

        $tests['badheader1'] = [
            'h.p.',
            '',
            ['Exception', 'Could not read JWT header']
        ];

        $header = base64_encode(json_encode(['key' => 'val']));
        $tests['invalidheader1'] = [
            $header . '.p.s',
            '',
            ['Exception', 'Invalid JWT header']
        ];

        $header = base64_encode(json_encode(['alg' => 'ROT13']));
        $tests['badalg1'] = [
            $header . '.p.s',
            '',
            ['Exception', 'JWS Alg or JWE not supported']
        ];

        $header = base64_encode(json_encode(['alg' => 'RS256']));
        $payload = base64_encode(json_encode(['payload' => 'found']));
        $tests['keyrequired'] = [
            $header . '.' . $payload . '.s',
            '',
            ['Exception', 'Key required for signed JWT.']
        ];

        $header = base64_encode(json_encode(['alg' => 'none']));
        $payload = 'p';
        $tests['badpayload1'] = [
            $header . '.' . $payload . '.',
            '',
            ['Exception', 'Could not read JWT payload.']
        ];

        $header = base64_encode(json_encode(['alg' => 'none']));
        $payload = base64_encode('nothing');
        $tests['badpayload2'] = [
            $header . '.' . $payload . '.',
            '',
            ['Exception', 'Could not read JWT payload.']
        ];

        $header = ['alg' => 'none'];
        $payload = ['payload' => 'found'];
        $headerenc = base64_encode(json_encode($header));
        $payloadenc = base64_encode(json_encode($payload));
        $expected = [$header, $payload];
        $tests['goodpayload1'] = [
            $headerenc . '.' . $payloadenc . '.s',
            $expected,
            []
        ];

        return $tests;
    }

    /**
     * Test decode.
     *
     * @dataProvider dataprovider_decode
     */
    public function test_decode($encodedjwt, $expectedresult, $expectedexception)
    {
        if (!empty($expectedexception)) {
            $this->setExpectedException($expectedexception[0], $expectedexception[1]);
        }
        $actualresult = \microsoft\aadphp\tests\MockJWT::decode($encodedjwt);
        $this->assertEquals($expectedresult, $actualresult);
    }
}
