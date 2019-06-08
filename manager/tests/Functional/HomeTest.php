<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class HomeTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            AuthFixture::class,
        ]);
    }

    public function testGuest(): void
    {
        $client = $this->makeClient();
        $client->request('GET', '/');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame('http://localhost/login', $client->getResponse()->headers->get('Location'));
    }

    public function testUser(): void
    {
        $client = $this->makeClient(false, AuthFixture::userCredentials());
        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Home', $crawler->filter('title')->text());
    }

    public function testAdmin(): void
    {
        $client = $this->makeClient(false, AuthFixture::userCredentials());
        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Home', $crawler->filter('title')->text());
    }
}
