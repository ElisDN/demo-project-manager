<?php

declare(strict_types=1);

namespace App\Tests\Functional\Users;

use App\Tests\Functional\AuthFixture;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class IndexTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            AuthFixture::class,
            UsersFixture::class,
        ]);
    }

    public function testGuest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/users');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame('http://localhost/login', $client->getResponse()->headers->get('Location'));
    }

    public function testUser(): void
    {
        $client = $this->makeClient(false, AuthFixture::userCredentials());
        $client->request('GET', '/users');

        $this->assertSame(403, $client->getResponse()->getStatusCode());
    }

    public function testAdmin(): void
    {
        $client = $this->makeClient(false, AuthFixture::adminCredentials());
        $crawler = $client->request('GET', '/users');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Users', $crawler->filter('title')->text());
    }
}
