<?php

declare(strict_types=1);

namespace App\Tests\Functional\Users;

use App\Tests\Functional\AuthFixture;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class CreateTest extends WebTestCase
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
        $client->request('GET', '/users/create');

        $this->assertSame(403, $client->getResponse()->getStatusCode());
    }

    public function testGet(): void
    {
        $client = $this->makeClient(false, AuthFixture::adminCredentials());
        $crawler = $client->request('GET', '/users/create');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Users', $crawler->filter('title')->text());
    }

    public function testCreate(): void
    {
        $client = $this->makeClient(false, AuthFixture::adminCredentials());
        $client->request('GET', '/users/create');

        $client->submitForm('Create', [
            'form[firstName]' => 'Tom',
            'form[lastName]' => 'Bent',
            'form[email]' => 'tom-bent@app.test',
        ]);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Users', $crawler->filter('title')->text());
        $this->assertContains('Tom Bent', $crawler->filter('body')->text());
        $this->assertContains('tom-bent@app.test', $crawler->filter('body')->text());
    }

    public function testNotValid(): void
    {
        $client = $this->makeClient(false, AuthFixture::adminCredentials());
        $client->request('GET', '/users/create');

        $crawler = $client->submitForm('Create', [
            'form[firstName]' => '',
            'form[lastName]' => '',
            'form[email]' => 'not-email',
        ]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertContains('This value should not be blank.', $crawler
            ->filter('#form_firstName')->parents()->first()->filter('.form-error-message')->text());

        $this->assertContains('This value should not be blank.', $crawler
            ->filter('#form_lastName')->parents()->first()->filter('.form-error-message')->text());

        $this->assertContains('This value is not a valid email address.', $crawler
            ->filter('#form_email')->parents()->first()->filter('.form-error-message')->text());
    }

    public function testExists(): void
    {
        $client = $this->makeClient(false, AuthFixture::adminCredentials());
        $client->request('GET', '/users/create');

        $crawler = $client->submitForm('Create', [
            'form[firstName]' => 'Tom',
            'form[lastName]' => 'Bent',
            'form[email]' => 'exesting-user@app.test',
        ]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertContains('User with this email already exists.', $crawler->filter('.alert.alert-danger')->text());
    }
}
