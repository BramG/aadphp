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
 * Tests Client.
 *
 * @group aadphp
 * @codeCoverageIgnore
 */
class OIDCClientTest extends \PHPUnit_Framework_TestCase
{

    private $httpclient;
    private $storage;
    private $client;

    /*
     * Setup required classes.
     */
    public function setUp()
    {
        parent::setUp();

        $this->httpclient = new \microsoft\aadphp\tests\MockHttpClient();
        $this->storage = new \microsoft\aadphp\tests\MockStorage();
        $this->client = new \microsoft\aadphp\OIDC\Client($this->httpclient, $this->storage);
    }

    /**
     * Test getting and setting credentials.
     */
    public function test_creds_getters_and_setters()
    {

        $this->assertNull($this->client->get_clientid());
        $this->assertNull($this->client->get_clientsecret());
        $this->assertNull($this->client->get_redirecturi());
        $this->assertNull($this->client->get_resource());

        $id = 'id';
        $secret = 'secret';
        $redirecturi = 'redirecturi';
        $resource = 'resource';
        $validauthflow = 'hybrid';
        $this->client->set_clientid($id);
        $this->client->set_clientsecret($secret);
        $this->client->set_redirecturi($redirecturi);
        $this->client->set_resource($resource);
        $this->client->set_authflow($validauthflow);

        $this->assertEquals($id, $this->client->get_clientid());
        $this->assertEquals($secret, $this->client->get_clientsecret());
        $this->assertEquals($redirecturi, $this->client->get_redirecturi());
        $this->assertEquals($resource, $this->client->get_resource());
        $this->assertEquals($validauthflow, $this->client->get_authflow());
    }

    /**
     * Dataprovider returning endpoints.
     *
     * @return array Array of arrays of test parameters.
     */
    public function dataprovider_endpoints()
    {
        $tests = [];

        $tests['oneinvalid'] = [
            ['auth' => 100],
            ['\microsoft\aadphp\AADPHPException', 'Invalid Endpoint URI received.']
        ];

        $tests['oneinvalidonevalid1'] = [
            ['auth' => 100, 'token' => 'http://example.com/token'],
            ['\microsoft\aadphp\AADPHPException', 'Invalid Endpoint URI received.']
        ];

        $tests['oneinvalidonevalid2'] = [
            ['token' => 'http://example.com/token', 'auth' => 100],
            ['\microsoft\aadphp\AADPHPException', 'Invalid Endpoint URI received.']
        ];

        $tests['onevalid'] = [
            ['token' => 'http://example.com/token'],
            []
        ];

        $tests['twovalid'] = [
            ['auth' => 'http://example.com/auth', 'token' => 'http://example.com/token'],
            []
        ];

        return $tests;
    }

    /**
     * Test setting and getting endpoints.
     *
     * @dataProvider dataprovider_endpoints
     */
    public function test_endpoints_getters_and_setters($endpoints, $expectedexception)
    {
        if (!empty($expectedexception)) {
            $this->setExpectedException($expectedexception[0], $expectedexception[1]);
        }

        if (isset($endpoints['auth'])) {
            $this->client->set_authendpoint($endpoints['auth']);
            $this->assertEquals($endpoints['auth'], $this->client->get_authendpoint());
        }
        if (isset($endpoints['token'])) {
            $this->client->set_tokenendpoint($endpoints['token']);
            $this->assertEquals($endpoints['token'], $this->client->get_tokenendpoint());
        }
    }

    /**
     * Dataprovider returning token parameters.
     *
     * @return array Array of arrays of token parameters.
     */
    public function dataprovider_token()
    {

        $tokens = [];
        $access_token = array(
            'access_token' => 'foobar',
            'token_type' => 'bearer',
            'id_token' => 'eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJub25jZSI6InRl'
                        . 'c3Rfbm9uY2UiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5l'
                        . 'dC83Mjc0MDZhYy03MDY4LTQ4ZmEtOTJiOS1jMmQ2NzIxMWJjNTAv'
                        . 'IiwiaWF0IjpudWxsLCJleHAiOiIyNTU0NDE2MDAwIiwiYXVkIjpb'
                        . 'InRlc3RfY2xpZW50X2lkIl0sInN1YiI6InFVOHhrczltSHFuVjZRM'
                        . 'zR6aDdTQVpvY2loOUV6cnJJOW1wVlhPSWJWQTgiLCJ2ZXIiOiIxLj'
                        . 'AiLCJ0aWQiOiI3Mjc0MDZhYy03MDY4LTQ4ZmEtOTJiOS1jMmQ2NzI'
                        . 'xMWJjNTAiLCJvaWQiOiI3ZjhlMTk2OS04YjgxLTQzOGMtOGQ0ZS1h'
                        . 'ZDZmNTYyYjI4YmIiLCJ1cG4iOiJmb29iYXJAdGVzdC5vbm1pY3Jvc'
                        . '29mdC5jb20iLCJnaXZlbl9uYW1lIjoiZm9vIiwiZmFtaWx5X25hbW'
                        . 'UiOiJiYXIiLCJuYW1lIjoiZm9vIGJhciIsInVuaXF1ZV9uYW1lIjoi'
                        . 'Zm9vYmFyQHRlc3Qub25taWNyb3NvZnQuY29tIiwicHdkX2V4cCI6Ij'
                        . 'Q3MzMwOTY4IiwicHdkX3VybCI6Imh0dHBzOi8vcG9ydGFsLm1pY3J'
                        . 'vc29mdG9ubGluZS5jb20vQ2hhbmdlUGFzc3dvcmQuYXNweCJ9.Bw0'
                        . 'quLcPi9tnWdOu_71y6Pjp6w1Orx1ZA2gy-9Qlcb_sIJnzwqrYnD5S'
                        . 'FG_OsshtSB0s9q98lAii-gLtzTd9YjKylJtD7gJ-v62VSqcvXEfsZ'
                        . 'lZ9TE43NjucNtW4xahj0S7dVxI1pMp-WRLxmp18x1qFE9UvXMIhmn'
                        . '91ue_1ROg',
            'expires_in' => 3600,
            'expires_on' => 1423650396,
            'not_before' => 1423646496
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
     * Test authorization flow.
     *
     * @dataProvider dataprovider_token
     */
    public function test_authorization_flow($token)
    {

        $this->storage->store_state('test_state', 'test_nonce', array());

        $request = array(
            'code' => 'test_code',
            'state' => 'test_state',
            'session_state' => 'test_session_state'
        );

        $this->httpclient->set_response(json_encode($token['access_token'], true));

        $this->client->set_tokenendpoint('https://test.onmicrosoft.com/token');
        $this->client->set_authendpoint('https://test.onmicrosoft.com/authorize');
        $this->client->set_clientid('test_client_id');
        $this->client->set_clientsecret('test_client_secret');
        $this->client->set_redirecturi('http://test.com/redirect.php');

//        list($idtoken, $tokenparams, $stateparams) = $this->client->handle_auth_response($request);
//
//        $id_token_claims = $token['id_token_claims'];
//        // Test id_token claims.
//        $this->assertEquals($idtoken->claim('oid'), $id_token_claims['oid']);
//        $this->assertEquals($idtoken->claim('upn'), $id_token_claims['upn']);
//        $this->assertEquals($idtoken->claim('given_name'), $id_token_claims['given_name']);
//        $this->assertEquals($idtoken->claim('family_name'), $id_token_claims['family_name']);
//        $this->assertEquals($idtoken->claim('name'), $id_token_claims['name']);
//        $this->assertEquals($idtoken->claim('unique_name'), $id_token_claims['unique_name']);
    }

    /**
     * Test authorization flow.
     *
     * @dataProvider dataprovider_token
     */
    public function test_tokenrequest($token)
    {

        $this->httpclient->set_response(json_encode($token['access_token'], true));

        $this->client->set_tokenendpoint('https://test.onmicrosoft.com/token');
        $this->client->set_authendpoint('https://test.onmicrosoft.com/authorize');
        $this->client->set_clientid('test_client_id');
        $this->client->set_clientsecret('test_client_secret');
        $this->client->set_redirecturi('http://test.com/redirect.php');

        $returned = $this->client->tokenrequest('test_code');

        // Test access token.
        $this->assertEquals($returned, $token['access_token']);
    }

    /**
     * Test hybrid flow.
     *
     * @dataProvider dataprovider_token
     */
    public function test_hybrid_flow($token)
    {

        $this->storage->store_state('test_state', 'test_nonce', array());
        $this->client->set_authflow('hybrid');

        $request = array(
            'code' => 'test_code',
            'state' => 'test_state',
            'session_state' => 'test_session_state',
            'id_token' => $token['id_token']
        );

        $this->httpclient->set_response($request);

        $this->client->set_tokenendpoint('https://test.onmicrosoft.com/token');
        $this->client->set_authendpoint('https://test.onmicrosoft.com/authorize');
        $this->client->set_clientid('test_client_id');
        $this->client->set_clientsecret('test_client_secret');
        $this->client->set_redirecturi('http://test.com/redirect.php');

        list($idtoken, $stateparams) = $this->client->handle_id_token($request);

        $id_token_claims = $token['id_token_claims'];
        // Test id_token claims.
        $this->assertEquals($idtoken->claim('oid'), $id_token_claims['oid']);
        $this->assertEquals($idtoken->claim('upn'), $id_token_claims['upn']);
        $this->assertEquals($idtoken->claim('given_name'), $id_token_claims['given_name']);
        $this->assertEquals($idtoken->claim('family_name'), $id_token_claims['family_name']);
        $this->assertEquals($idtoken->claim('name'), $id_token_claims['name']);
        $this->assertEquals($idtoken->claim('unique_name'), $id_token_claims['unique_name']);
    }
}
