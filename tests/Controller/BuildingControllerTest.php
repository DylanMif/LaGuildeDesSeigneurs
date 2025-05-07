<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

final class BuildingControllerTest extends WebTestCase
{
    private $client;
    private $content; // Contenu de la réponse
    private static $identifier; // Identifier du Character
    private static $userId;

    public function setUp(): void {
        $this->client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('contact@example.com');
        self::$userId = $testUser->getId();
        $this->client->loginUser($testUser);
    }

    public function testCreate()
    {
        $this->client->request(
            'POST',
            '/buildings',
            array(),// Parameters
            array(),// Files
            array('CONTENT_TYPE' => 'application/json'),// Server
            <<<JSON
            {
            "name": "Château Silken",
            "caste": "Archer",
            "image": "/buildings/chateau-silken.webp",
            "strength": 1200
            }
            JSON
            );
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

        // Tests with page
        $this->client->request('GET', '/buildings?page=1');
        $this->assertResponseCode(200);
        $this->assertJsonResponse();
        // Tests with page and size
        $this->client->request('GET', '/buildings?page=1&size=1');
        $this->assertResponseCode(200);
        $this->assertJsonResponse();
        // Tests with size
        $this->client->request('GET', '/buildings?size=1');
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
        $this->client->request(
            'PUT',
            '/buildings/' . self::$identifier,
            array(),// Parameters
            array(),// Files
            array('CONTENT_TYPE' => 'application/json'),// Server
            <<<JSON
            {
            "name": "Château Oakenfield",
            "caste": "Erudit"
            }
            JSON
            );
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
