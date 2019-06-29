<?php

declare(strict_types=1);

namespace App\Tests\Functional\OAuth;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Name;
use App\Tests\Builder\User\UserBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Grants;

class OAuthFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = (new UserBuilder())
            ->viaEmail(
                new Email('oauth-password-user@app.test'),
                '$2y$12$qwnND33o8DGWvFoepotSju7eTAQ6gzLD/zy6W8NCVtiHPbkybz.w6' // 'password'
            )
            ->withName(new Name('OAuth', 'User'))
            ->confirmed()
            ->build();

        $manager->persist($user);

        $client = new Client('oauth', 'secret');
        $client->setGrants(new Grant(OAuth2Grants::PASSWORD));

        $manager->persist($client);

        $manager->flush();
    }
}
