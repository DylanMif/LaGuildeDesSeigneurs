<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BuildingControllerTest extends WebTestCase
{
    private $client;
    private $content; // Contenu de la réponse
    private static $identifier; // Identifier du Building
    private static $userId;

    public function setUp(): void
    {
        $this->client = static::createClient();

        // Récupération du User
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('contact@example.com');
        self::$userId = $testUser->getId();
        $this->client->loginUser($testUser);
    }

    // -------------------- ASSERTS --------------------

    // Asserts that Response code is 204
    public function assertResponseCode(int $code)
    {
        $response = $this->client->getResponse();

        $this->assertEquals($code, $response->getStatusCode());
    }

    // Asserts that 'identifier' is present in the Response
    public function assertIdentifier()
    {
        $this->assertArrayHasKey('identifier', $this->content);
    }

    // Asserts that a Response is in json
    public function assertJsonResponse()
    {
        $response = $this->client->getResponse();
        $this->content = json_decode($response->getContent(), true, 50);

        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'), $response->headers);
    }

    // Defines identifier
    public function defineIdentifier()
    {
        self::$identifier = $this->content['identifier'];
    }

    // -------------------- TESTS --------------------

    // Tests creates
    public function testCreate()
    {
        $this->client->request(
            'POST',
            '/buildings/',
            array(),// Parameters
            array(),// Files
            array('CONTENT_TYPE' => 'application/json'),// Server
            <<<JSON
            {
            "name": "Château Silken",
            "caste": "Archer",
            "strength": "1200",
            "image": "/buildings/chateau-silken.webp",
            "rating": 3
            }
            JSON
        );
        $this->assertResponseCode(201);
        $this->assertJsonResponse();
        $this->defineIdentifier();
        $this->assertIdentifier();
    }

    // Test Display
    public function testDisplay(): void
    {
        $this->client->request('GET', '/buildings/' . self::$identifier);

        $this->assertResponseCode(200);
        $this->assertJsonResponse();
        $this->assertIdentifier();
    }

    // Tests index
    public function testIndex()
    {
        // Tests with default values
        $this->client->request('GET', '/buildings/');

        $this->assertResponseCode(200);
        $this->assertJsonResponse();

        // Tests with page
        $this->client->request('GET', '/buildings/?page=1');

        $this->assertResponseCode(200);
        $this->assertJsonResponse();

        // Tests with page and size
        $this->client->request('GET', '/buildings/?page=1&size=1');

        $this->assertResponseCode(200);
        $this->assertJsonResponse();

        // Tests with page and size
        $this->client->request('GET', '/buildings/?size=1');

        $this->assertResponseCode(200);
        $this->assertJsonResponse();
    }

    // Tests bad identifier
    public function testBadIdentifier()
    {
        $this->client->request('GET', '/buildings/badIdentifier');

        $this->assertError404();
    }

    // Asserts that Response returns 404
    public function assertError404()
    {
        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }

    // Tests inexisting identifier
    public function testInexistingIdentifier()
    {
        $this->client->request('GET', '/buildings/8f74f20597c5cf99dd42cd31331b7e6e2aeerror');
        
        $this->assertError404();
    }

    // Tests update
    public function testUpdate()
    {
        // Tests with partial data array
        $this->client->request(
            'PUT',
            '/buildings/' . self::$identifier,
            array(),// Parameters
            array(),// Files
            array('CONTENT_TYPE' => 'application/json'),// Server
            <<<JSON
            {
                "name": "Château Fort",
                "rating": 4
            }
            JSON
        );

        $this->assertResponseCode(204);
    }

    // Tests update
    public function testDelete()
    {
        $this->client->request('DELETE', '/buildings/' . self::$identifier);

        $this->assertResponseCode(204);
    }

    // Tests images
    public function testImages()
    {
        $this->client->request('GET', '/buildings/images');
        $this->assertJsonResponse();
        $this->client->request('GET', '/buildings/images/3');
        $this->assertJsonResponse();
    }
}
