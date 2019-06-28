<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Profile;

use App\Model\User\Entity\User\Id;
use App\Tests\Functional\DbWebTestCase;

class NameTest extends DbWebTestCase
{
    private const URI = '/api/profile/name';

    public function testGet(): void
    {
        $this->client->setServerParameters(ProfileFixture::userCredentials());
        $this->client->request('GET', self::URI);

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testPost(): void
    {
        $this->client->setServerParameters(ProfileFixture::userCredentials());
        $this->client->request('POST', self::URI);

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testPut(): void
    {
        $this->client->setServerParameters(ProfileFixture::userCredentials());

        $this->client->request('PUT', self::URI, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'id' => Id::next(), // fake id
            'first' => 'Tom',
            'last' => 'Bent',
        ]));

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());
        $data = json_decode($content, true);

        self::assertEquals([], $data);

        $this->client->request('GET', '/api/profile');
        self::assertJson($content = $this->client->getResponse()->getContent());
        $data = json_decode($content, true);

        self::assertArraySubset([
            'name' => [
                'first' => 'Tom',
                'last' => 'Bent',
            ],
        ], $data);
    }

    public function testNotValid(): void
    {
        $this->client->setServerParameters(ProfileFixture::userCredentials());

        $this->client->request('PUT', self::URI, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());
        $data = json_decode($content, true);

        self::assertArraySubset([
            'violations' => [
                ['propertyPath' => 'first', 'title' => 'This value should not be blank.'],
                ['propertyPath' => 'last', 'title' => 'This value should not be blank.'],
            ],
        ], $data);
    }
}