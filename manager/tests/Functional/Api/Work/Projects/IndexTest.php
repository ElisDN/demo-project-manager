<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Work\Projects;

use App\Tests\Functional\DbWebTestCase;

class IndexTest extends DbWebTestCase
{
    private const URI = '/api/work/projects';

    public function testGuest(): void
    {
        $this->client->request('GET', self::URI);

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testAdmin(): void
    {
        $this->client->setServerParameters(IndexFixture::adminCredentials());
        $this->client->request('GET', self::URI . '?filter[name]=Project+Test+Index');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([
            'items' => [
                [
                    'id' => IndexFixture::ID_1,
                    'name' => 'Project Test Index First',
                    'status' => 'active',
                ],
                [
                    'id' => IndexFixture::ID_4,
                    'name' => 'Project Test Index Fourth',
                    'status' => 'active',
                ],
                [
                    'id' => IndexFixture::ID_3,
                    'name' => 'Project Test Index Third',
                    'status' => 'active',
                ],
            ],
            'pagination' => [
                'total' => 3,
                'count' => 3,
                'per_page' => 50,
                'page' => 1,
                'pages' => 1,
            ],
        ], $data);
    }

    public function testUser(): void
    {
        $this->client->setServerParameters(IndexFixture::userCredentials());
        $this->client->request('GET', self::URI);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([
            'items' => [
                [
                    'id' => IndexFixture::ID_4,
                    'name' => 'Project Test Index Fourth',
                    'status' => 'active',
                ],
            ],
            'pagination' => [
                'total' => 1,
                'count' => 1,
                'per_page' => 50,
                'page' => 1,
                'pages' => 1,
            ],
        ], $data);
    }

    public function testFilterStatus(): void
    {
        $this->client->setServerParameters(IndexFixture::userCredentials());
        $this->client->request('GET', self::URI . '?filter[status]=archived');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([
            'items' => [
                [
                    'id' => IndexFixture::ID_2,
                    'name' => 'Project Test Index Second',
                    'status' => 'archived',
                ],
            ],
            'pagination' => [
                'total' => 1,
                'count' => 1,
                'per_page' => 50,
                'page' => 1,
                'pages' => 1,
            ],
        ], $data);
    }

    public function testFilterName(): void
    {
        $this->client->setServerParameters(IndexFixture::userCredentials());
        $this->client->request('GET', self::URI . '?filter[name]=test+index+four');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([
            'items' => [
                [
                    'id' => IndexFixture::ID_4,
                    'name' => 'Project Test Index Fourth',
                    'status' => 'active',
                ],
            ],
            'pagination' => [
                'total' => 1,
                'count' => 1,
                'per_page' => 50,
                'page' => 1,
                'pages' => 1,
            ],
        ], $data);
    }
}