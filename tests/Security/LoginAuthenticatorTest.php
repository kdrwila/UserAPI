<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    private $usedMail = '';

    public function testLoginWrongMethods()
    {
        $client = static::createClient();

        $client->request('GET', '/api/sign-in');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('PUT', '/api/sign-in');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('DELETE', '/api/sign-in');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/sign-in');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());
    }

    public function testCorrectLogin()
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
            '{"name":"Karol", "email":"'. $this->usedMail .'@gmail.com", "password":"test"}');

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
    }

    public function testIncorrectLogin()
    {
        $client = static::createClient();

        $client->request(
            'POST', 
            '/api/sign-in',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            '{"password":"test"}');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client->request(
            'POST', 
            '/api/sign-in',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            '{"email":"", "password":"test"}');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client->request(
            'POST', 
            '/api/sign-in',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            '{email":"'. bin2hex(random_bytes(16)) .'@yahoo.com", "password":"test"}');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}

?>