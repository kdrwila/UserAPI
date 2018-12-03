<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private $usedMail = '';

    public function testRegisterWrongMethods()
    {
        $client = static::createClient();

        $client->request('GET', '/api/sign-up');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('PUT', '/api/sign-up');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('DELETE', '/api/sign-up');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/sign-up');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());
    }

    public function testCorrectRegister()
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
    }

    public function testIncorrectRegister()
    {
        $client = static::createClient();

        $client->request(
            'POST', 
            '/api/sign-up',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            '{"name":"John", "email":"'. $this->usedMail .'@gmail.com", "password":"test"}');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $client->request(
            'POST', 
            '/api/sign-up',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            '{"name":"John", "password":"test"}');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $client->request(
            'POST', 
            '/api/sign-up',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            '{"name":"John", "email":"", "password":"test"}');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }
}

?>