<?php

declare(strict_types=1);

namespace App\Tests\Functional\Users;

use App\Model\User\Entity\User\Id;
use App\Tests\Functional\AuthFixture;
use App\Tests\Functional\DbWebTestCase;

class ShowTest extends DbWebTestCase
{
    public function testGuest(): void
    {
        $this->client->request('GET', '/users/' . UsersFixture::EXISTING_ID);

        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $this->assertSame('http://localhost/login', $this->client->getResponse()->headers->get('Location'));
    }

    public function testUser(): void
    {
        $this->client->setServerParameters(AuthFixture::userCredentials());
        $this->client->request('GET', '/users/' . UsersFixture::EXISTING_ID);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testGet(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $crawler = $this->client->request('GET', '/users/' . UsersFixture::EXISTING_ID);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Users', $crawler->filter('title')->text());
        $this->assertContains('Show User', $crawler->filter('table')->text());
    }

    public function testNotFound(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('GET', '/users/' . Id::next()->getValue());

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }
}
