<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

class ClientFixture extends Fixture
{
    public const REFERENCE_CLIENT = 'test_oauth_client';

    public function load(ObjectManager $manager): void
    {
        $client = new Client('test', 'secret');
        $manager->persist($client);

        $this->setReference(self::REFERENCE_CLIENT, $client);

        $manager->flush();
    }
}
