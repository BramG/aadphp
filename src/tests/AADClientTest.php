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
 * @author Sushant Gawali <sushant@introp.net>
 * @license MIT
 * @copyright (C) 2016 onwards Microsoft Corporation (http://microsoft.com/)
 */

namespace microsoft\aadphp\tests;

/**
 * Tests AAD Client.
 *
 * @group aadphp
 * @codeCoverageIgnore
 */
class AADClientTest extends \PHPUnit_Framework_TestCase
{

    /*
     * Setup required classes.
     */
    public function setUp()
    {
        parent::setUp();

        $this->httpclient = new \microsoft\aadphp\tests\MockHttpClient();
        $this->storage = new \microsoft\aadphp\tests\MockStorage();
        $this->client = new \microsoft\aadphp\AAD\Client($this->httpclient, $this->storage);
    }

    /**
     * Dataprovider returning token parameters.
     *
     * @return array Array of arrays of token parameters.
     */
    public function dataprovider_token()
    {
        $access_token = array(
            'access_token' => 'foobar',
            'token_type' => 'bearer',
            'id_token' => 'eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJpc3MiOiJodHRw'
                        . 'czovL3N0cy53aW5kb3dzLm5ldC83Mjc0MDZhYy03MDY4LTQ4ZmEt'
                        . 'OTJiOS1jMmQ2NzIxMWJjNTAvIiwiaWF0IjpudWxsLCJleHAiOm51'
                        . 'bGwsImF1ZCI6IjAyOWNjMDEwLWJiNzQtNGQyYi1hMDQwLWY5Y2Vk'
                        . 'M2ZkMmM3NiIsInN1YiI6InFVOHhrczltSHFuVjZRMzR6aDdTQVpvY'
                        . '2loOUV6cnJJOW1wVlhPSWJWQTgiLCJ2ZXIiOiIxLjAiLCJ0aWQiOi'
                        . 'I3Mjc0MDZhYy03MDY4LTQ4ZmEtOTJiOS1jMmQ2NzIxMWJjNTAiLCJ'
                        . 'vaWQiOiI3ZjhlMTk2OS04YjgxLTQzOGMtOGQ0ZS1hZDZmNTYyYjI4'
                        . 'YmIiLCJ1cG4iOiJmb29iYXJAdGVzdC5vbm1pY3Jvc29mdC5jb20iL'
                        . 'CJnaXZlbl9uYW1lIjoiZm9vIiwiZmFtaWx5X25hbWUiOiJiYXIiLC'
                        . 'JuYW1lIjoiZm9vIGJhciIsInVuaXF1ZV9uYW1lIjoiZm9vYmFyQHRl'
                        . 'c3Qub25taWNyb3NvZnQuY29tIiwicHdkX2V4cCI6IjQ3MzMwOTY4Ii'
                        . 'wicHdkX3VybCI6Imh0dHBzOi8vcG9ydGFsLm1pY3Jvc29mdG9ubGlu'
                        . 'ZS5jb20vQ2hhbmdlUGFzc3dvcmQuYXNweCJ9.1g_p0voKTSziDdmwy'
                        . 'ZHYQVP8r5rjoavOrpY0nMM_t0K42QnT667cTGTCdE1lA_8dGS5hCol'
                        . '-arZ6jcudklrRq4zulIlJw7LzxA8dsMQB8yr9z_SWg0HHkEb56OUv1'
                        . 'xfXUlwWa-SSVKxsowAbM-ZiDwlOmiHSayv9NqYJrbwbMI8',
            'expires_in' => 3600,
            'expires_on' => 1423650396,
            'not_before' => 1423646496,
            'refresh_token' => 'foobar',
            'resource' => 'https://graph.windows.net',
            'scope' => 'User.Read'
        );

        $id_token = $access_token['id_token'];

        $id_token_claims = array(
            'oid' => '7f8e1969-8b81-438c-8d4e-ad6f562b28bb',
            'upn' => 'foobar@test.onmicrosoft.com',
            'given_name' => 'foo',
            'family_name' => 'bar',
            'name' => 'foo bar',
            'unique_name' => 'foobar@test.onmicrosoft.com'
        );

        $tokens['data'] = [
            [
                'access_token' => $access_token,
                'id_token' => $id_token,
                'id_token_claims' => $id_token_claims
            ]
        ];

        return $tokens;
    }

    /**
     * Test resource owner credentials flow.
     * @dataProvider dataprovider_token
     */
    public function test_rocred_flow($token)
    {

        $this->httpclient->set_response(json_encode($token['access_token'], true));

        $this->client->set_tokenendpoint('https://test.onmicrosoft.com');

        $returned = $this->client->rocredsrequest('o365_email', 'o365_password');

        // Test access token.
        $this->assertEquals($returned, $token['access_token']);

        $id_token = \microsoft\aadphp\AAD\IDToken::instance_from_encoded($returned['id_token']);

        $id_token_claims = $token['id_token_claims'];

        // Test id_token claims.
        $this->assertEquals($id_token->claim('oid'), $id_token_claims['oid']);
        $this->assertEquals($id_token->claim('upn'), $id_token_claims['upn']);
        $this->assertEquals($id_token->claim('given_name'), $id_token_claims['given_name']);
        $this->assertEquals($id_token->claim('family_name'), $id_token_claims['family_name']);
        $this->assertEquals($id_token->claim('name'), $id_token_claims['name']);
        $this->assertEquals($id_token->claim('unique_name'), $id_token_claims['unique_name']);
    }
}