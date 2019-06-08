<?php

declare(strict_types=1);

namespace App\Tests\Functional\Users;

use App\Model\User\Entity\User\Id;
use App\Tests\Functional\AuthFixture;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class ShowTest extends WebTestCase
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
        $client->request('GET', '/users/' . UsersFixture::EXISTING_ID);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame('http://localhost/login', $client->getResponse()->headers->get('Location'));
    }

    public function testUser(): void
    {
        $client = $this->makeClient(false, AuthFixture::userCredentials());
        $client->request('GET', '/users/' . UsersFixture::EXISTING_ID);

        $this->assertSame(403, $client->getResponse()->getStatusCode());
    }

    public function testGet(): void
    {
        $client = $this->makeClient(false, AuthFixture::adminCredentials());
        $crawler = $client->request('GET', '/users/' . UsersFixture::EXISTING_ID);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Users', $crawler->filter('title')->text());
        $this->assertContains('Show User', $crawler->filter('table')->text());
    }

    public function testNotFound(): void
    {
        $client = $this->makeClient(false, AuthFixture::adminCredentials());
        $client->request('GET', '/users/' . Id::next()->getValue());

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
