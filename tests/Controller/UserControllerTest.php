<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $usedMail = '';
    private $apiToken = '';
    private $id = 0;

    public function testLoginWrongMethods()
    {
        $client = static::createClient();

        $client->request('POST', '/api/me');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('PUT', '/api/me');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('DELETE', '/api/me');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('PATCH', '/api/me');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('POST', '/api/users');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('PUT', '/api/users');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('DELETE', '/api/users');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('PATCH', '/api/users');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('POST', '/api/users/email/gmail');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('PUT', '/api/users/email/gmail');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('DELETE', '/api/users/email/gmail');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('PATCH', '/api/users/email/gmail');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('PUT', '/api/users/1');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());
    }

    public function testEndpoints()
    {
        $client = static::createClient();

        $this->usedMail = bin2hex(random_bytes(16));

        $client->request(
            'POST', 
            '/api/sign-up',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            '{"name":"John", "email":"'. $this->usedMail .'@gmail.com", "password":"test"}');

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertContains('successful', $client->getResponse()->getContent());

        $client->request(
            'POST', 
            '/api/sign-in',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            '{"email":"'. $this->usedMail .'@gmail.com", "password":"test"}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('successfully', $client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->apiToken = $data['apiToken'];
        $this->id = $data['id'];

        $client->request(
            'GET', 
            '/api/users',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => $this->apiToken
            ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('json', $client->getResponse()->headers->get('Content-type'));

        $client->request(
            'GET', 
            '/api/users/'. (int)$this->id,
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => $this->apiToken
            ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('json', $client->getResponse()->headers->get('Content-type'));

        $client->request(
            'GET', 
            '/api/me',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => $this->apiToken
            ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('json', $client->getResponse()->headers->get('Content-type'));

        $client->request(
            'GET', 
            '/api/users/email/yahoo',
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => $this->apiToken
            ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('json', $client->getResponse()->headers->get('Content-type'));

        $client->request(
            'PATCH', 
            '/api/users/'. (int)$this->id,
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => $this->apiToken
            ),
            '{"name":"John", "password":"test"}');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('json', $client->getResponse()->headers->get('Content-type'));

        $client->request(
            'DELETE', 
            '/api/users/'. (int)($this->id - 1),
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => $this->apiToken
            ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('json', $client->getResponse()->headers->get('Content-type'));

        $client->request(
            'GET', 
            '/api/users/'. (int)($this->id - 1),
            array(),
            array(),
            array(
                'HTTP_X_AUTH_TOKEN' => $this->apiToken
            ));

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}

?>