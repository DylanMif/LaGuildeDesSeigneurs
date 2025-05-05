<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BuildingControllerTest extends WebTestCase
{
    private $client;
    private $content; // Contenu de la réponse
    private static $identifier; // Identifier du Character

    public function setUp(): void {
        $this->client = static::createClient();
    }

    public function testCreate()
    {
        $this->client->request('POST', '/buildings');
        $this->assertResponseCode(201);
        $this->assertJsonResponse();
        $this->defineIdentifier();
        $this->assertIdentifier();
    }

    public function testDisplay(): void
    {
        $this->client->request('GET', '/buildings/' . self::$identifier);
        $this->assertResponseCode(200);
        $this->assertJsonResponse();
        $this->assertIdentifier();
    }

    // Asserts that a Response is in json
    public function assertJsonResponse()
    {
        $response = $this->client->getResponse();
        $this->content = json_decode($response->getContent(), true, 50);

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'), $response->headers);
    }

    public function testIndex()
    {
        $this->client->request('GET', '/buildings');
        $this->assertResponseCode(200);
        $this->assertJsonResponse();
    }

    public function testBadIdentifier()
    {
        $this->client->request('GET', '/buildings/badIdentifier');
        $this->assertError404();
    }

    public function assertError404()
    {
        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testInexistingIdentifier()
    {
        $this->client->request('GET', '/buildings/8f74f20597c5cf99dd42cd31331b7e6e2aeerror');
        $this->assertError404();
    }

    public function testUpdate()
    {
        $this->client->request('PUT', '/buildings/' . self::$identifier);
        $this->assertResponseCode(204);
    }

    public function assertResponseCode204()
    {
        $response = $this->client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDelete() {
        $this->client->request('DELETE', '/buildings/' . self::$identifier);
        $this->assertResponseCode(204);
    }

    public function assertResponseCode(int $code) {
        $response = $this->client->getResponse();
        $this->assertEquals($code, $response->getStatusCode());
    }

    public function assertIdentifier()
    {
        $this->assertArrayHasKey('identifier', $this->content);
    }
    // Defines identifier
    public function defineIdentifier()
    {
        self::$identifier = $this->content['identifier'];
    }
}
