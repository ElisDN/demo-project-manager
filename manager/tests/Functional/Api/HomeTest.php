<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Functional\DbWebTestCase;

class HomeTest extends DbWebTestCase
{
    public function testGet(): void
    {
        $this->client->request('GET', '/api/');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([
            'name' => 'JSON API',
        ], $data);
    }

    public function testPost(): void
    {
        $this->client->request('POST', '/api/');

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }
}
