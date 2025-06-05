<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $content;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    // Asserts that Response code is equal to $code
    public function assertResponseCode(int $code)
    {
        $response = $this->client->getResponse();
        $this->assertEquals($code, $response->getStatusCode());
    }
    // Asserts that a Response is in json
    public function assertJsonResponse()
    {
        $response = $this->client->getResponse();
        $this->content = json_decode($response->getContent(), true, 50);
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'), $response->headers);
    }

    // Tests signin
    public function testSignin()
    {
        $this->client->request(
            'POST',
            '/signin',
            array(),// Parameters
            array(),// Files
            array('CONTENT_TYPE' => 'application/json'),// Server
            <<<JSON
            {
                "username": "contact@example.com",
                "password": "StrongPassword*"
            }
            JSON
        );
        $this->assertResponseCode(200);
        $this->assertJsonResponse();
    }

    // Tests bad signin
    public function testBadSignin()
    {
        $this->client->request(
            'POST',
            '/signin',
            array(),// Parameters
            array(),// Files
            array('CONTENT_TYPE' => 'application/json'),// Server
            <<<JSON
            {
                "username": "contact@example.com",
                "password": "InvalidPassword*"
            }
            JSON
        );
        $this->assertResponseCode(401);
    }
}
