<?php

declare(strict_types=1);

namespace App\Tests\Functional\Users;

use App\Tests\Functional\AuthFixture;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexTest extends WebTestCase
{
    public function testGuest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/users');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame('http://localhost/login', $client->getResponse()->headers->get('Location'));
    }

    public function testUser(): void
    {
        $client = static::createClient([], AuthFixture::userCredentials());
        $client->request('GET', '/users');

        $this->assertSame(403, $client->getResponse()->getStatusCode());
    }

    public function testAdmin(): void
    {
        $client = static::createClient([], AuthFixture::adminCredentials());
        $crawler = $client->request('GET', '/users');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Users', $crawler->filter('title')->text());
    }
}
