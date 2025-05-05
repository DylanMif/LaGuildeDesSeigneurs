<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CharacterControllerTest extends WebTestCase
{
    private $client;

    public function setUp(): void {
        $this->client = static::createClient();
    }
    public function testDisplay(): void
    {
        $this->client->request('GET', '/characters/9417ae5f5f291d8309dc067e9ac4f463ab259428');
        $this->assertJsonResponse();
    }

    // Asserts that a Response is in json
    public function assertJsonResponse()
    {
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'), $response->headers);
    }

    public function testIndex()
    {
        $this->client->request('GET', '/characters');
        $this->assertJsonResponse();
    }

    public function testBadIdentifier()
    {
        $this->client->request('GET', '/characters/badIdentifier');
        $this->assertError404();
    }

    public function assertError404()
    {
        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testInexistingIdentifier()
    {
        $this->client->request('GET', '/characters/8f74f20597c5cf99dd42cd31331b7e6e2aeerror');
        $this->assertError404();
    }

    public function testUpdate()
    {
        $this->client->request('PUT', '/characters/07c9a2e936d3d156d34655d22b3906a378c57b30');
        $this->assertResponseCode204();
    }

    public function assertResponseCode204()
    {
        $response = $this->client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());
    }
}
