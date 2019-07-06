<?php

declare(strict_types=1);

namespace App\DataFixtures\OAuth;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant;
use Trikoder\Bundle\OAuth2Bundle\Model\RedirectUri;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Grants;

class ClientFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $client = new Client('app', 'secret');

        $client->setGrants(
            new Grant(OAuth2Grants::AUTHORIZATION_CODE),
            new Grant(OAuth2Grants::IMPLICIT),
            new Grant(OAuth2Grants::PASSWORD),
            new Grant(OAuth2Grants::CLIENT_CREDENTIALS),
            new Grant(OAuth2Grants::REFRESH_TOKEN),
        );

        $client->setScopes(new Scope('common'));
        $client->setRedirectUris(new RedirectUri('http://localhost:8080/docs/oauth2-redirect.html'));

        $manager->persist($client);

        $manager->flush();
    }
}