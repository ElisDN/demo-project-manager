<?php

declare(strict_types=1);

namespace App\Tests\Functional\OAuth;

use App\Tests\Functional\DbWebTestCase;

class PasswordTest extends DbWebTestCase
{
    private const URI = '/token';

    public function testMethod(): void
    {
        $this->client->request('GET', self::URI);
        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testSuccess(): void
    {
        $this->client->request('POST', self::URI, [
            'grant_type' => 'password',
            'username' => 'oauth-password-user@app.test',
            'password' => 'password',
            'client_id' => 'oauth',
            'client_secret' => 'secret',
            'access_type' => 'offline',
        ]);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertArraySubset([
            'token_type' => 'Bearer',
        ], $data);

        self::assertArrayHasKey('expires_in', $data);
        self::assertNotEmpty($data['expires_in']);

        self::assertArrayHasKey('access_token', $data);
        self::assertNotEmpty($data['access_token']);

        self::assertArrayHasKey('refresh_token', $data);
        self::assertNotEmpty($data['refresh_token']);
    }

    public function testInvalid(): void
    {
        $this->client->request('POST', self::URI, [
            'grant_type' => 'password',
            'username' => 'oauth-password-user@app.test',
            'password' => 'invalid',
            'client_id' => 'oauth',
            'client_secret' => 'secret',
        ]);

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }
}
