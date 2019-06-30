<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Work\Tasks;

use App\Tests\Functional\AuthFixture;
use App\Tests\Functional\DbWebTestCase;

class PlanTest extends DbWebTestCase
{
    private const URI = '/api/work/tasks/%s/plan';
    private const SHOW_URI = '/api/work/tasks/%s';

    public function testGet(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('GET', sprintf(self::URI, TaskFixture::TASK_IN_PROJECT_WITH_USER));

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testPost(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('POST', sprintf(self::URI, TaskFixture::TASK_IN_PROJECT_WITH_USER));

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testAdmin(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());

        $date = new \DateTimeImmutable('+1 day');

        $this->client->request('PUT', sprintf(self::URI, TaskFixture::TASK_IN_PROJECT_WITH_USER), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'date' => $date->format('Y-m-d H:i:s'),
        ]));

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());
        $data = json_decode($content, true);

        self::assertEquals([], $data);

        $this->client->request('GET', sprintf(self::SHOW_URI, TaskFixture::TASK_IN_PROJECT_WITH_USER));
        self::assertJson($content = $this->client->getResponse()->getContent());
        $data = json_decode($content, true);

        self::assertArraySubset([
            'plan_date' => $date->format(DATE_ATOM),
        ], $data);
    }

    public function testMember(): void
    {
        $this->client->setServerParameters(AuthFixture::userCredentials());

        $date = new \DateTimeImmutable('+1 day');

        $this->client->request('PUT', sprintf(self::URI, TaskFixture::TASK_IN_PROJECT_WITH_USER), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'date' => $date->format('Y-m-d H:i:s'),
        ]));

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testNotMember(): void
    {
        $this->client->setServerParameters(AuthFixture::userCredentials());

        $date = new \DateTimeImmutable('+1 day');

        $this->client->request('PUT', sprintf(self::URI, TaskFixture::TASK_IN_PROJECT_WITHOUT_USER), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'date' => $date->format('Y-m-d H:i:s'),
        ]));

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testNotValid(): void
    {
        $this->client->setServerParameters(AuthFixture::userCredentials());

        $this->client->request('PUT', sprintf(self::URI, TaskFixture::TASK_IN_PROJECT_WITH_USER));

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());
        $data = json_decode($content, true);

        self::assertArraySubset([
            'violations' => [
                ['propertyPath' => 'date', 'title' => 'This value should not be blank.'],
            ],
        ], $data);
    }
}
