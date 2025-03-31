<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CharacterControllerTest extends WebTestCase
{
    public function testDisplay(): void
    {
        $client = static::createClient();
        $client->request('GET', '/characters/9417ae5f5f291d8309dc067e9ac4f463ab259428');

        $this->assertJsonResponse($client->getResponse());
    }

    // Asserts that a Response is in json
    public function assertJsonResponse($response)
    {
        $this->assertResponseIsSuccessful();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'), $response->headers);
    }
}
