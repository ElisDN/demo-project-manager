<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User\SignUp;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\User;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = new User(
            $id = Id::next(),
            $date = new \DateTimeImmutable()
        );

        $user->signUpByEmail(
            $email = new Email('test@app.test'),
            $hash = 'hash',
            $token = 'token'
        );

        self::assertTrue($user->isWait());
        self::assertFalse($user->isActive());

        self::assertEquals($email, $user->getEmail());
        self::assertEquals($hash, $user->getPasswordHash());
        self::assertEquals($token, $user->getConfirmToken());
    }

    public function testAlready(): void
    {
        $user = new User(
            $id = Id::next(),
            $date = new \DateTimeImmutable()
        );

        $user->signUpByEmail(
            $email = new Email('test@app.test'),
            $hash = 'hash',
            $token = 'token'
        );

        $this->expectExceptionMessage('User is already signed up.');

        $user->signUpByEmail($email, $hash, $token);
    }
}
