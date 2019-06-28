<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Profile;

use App\Tests\Functional\DbWebTestCase;

class ShowTest extends DbWebTestCase
{
    private const URI = '/api/profile';

    public function testGuest(): void
    {
        $this->client->request('GET', self::URI);

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testUser(): void
    {
        $this->client->setServerParameters(ProfileFixture::userCredentials());
        $this->client->request('GET', self::URI);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([
            'id' => ProfileFixture::USER_ID,
            'email' => 'profile-user@app.test',
            'name' => [
                'first' => 'Profile',
                'last' => 'User',
            ],
            'networks' => [
                [
                    'name' => 'facebook',
                    'identity' => '1111',
                ]
            ],
        ], $data);
    }
}