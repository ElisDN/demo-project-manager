<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\User\Entity\User\Reset;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\ResetToken;
use App\Model\User\Entity\User\User;
use PHPUnit\Framework\TestCase;

class ResetTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = $this->buildSignedUpByEmailUser();

        $now = new \DateTimeImmutable();
        $token = new ResetToken('token', $now->modify('+1 day'));

        $user->requestPasswordReset($token, $now);

        self::assertNotNull($user->getResetToken());

        $user->passwordReset($now, $hash = 'hash');

        self::assertNull($user->getResetToken());
        self::assertEquals($hash, $user->getPasswordHash());
    }

    public function testExpiredToken(): void
    {
        $user = $this->buildSignedUpByEmailUser();

        $now = new \DateTimeImmutable();
        $token = new ResetToken('token', $now);

        $user->requestPasswordReset($token, $now);

        $this->expectExceptionMessage('Reset token is expired.');
        $user->passwordReset($now->modify('+1 day'), 'hash');
    }

    public function testNotRequested(): void
    {
        $user = $this->buildSignedUpByEmailUser();

        $now = new \DateTimeImmutable();

        $this->expectExceptionMessage('Resetting is not requested.');
        $user->passwordReset($now, 'hash');
    }

    private function buildSignedUpByEmailUser(): User
    {
        $user = $this->buildUser();

        $user->signUpByEmail(
            new Email('test@app.test'),
            'hash',
            $token = 'token'
        );

        return $user;
    }

    private function buildUser(): User
    {
        return new User(
            Id::next(),
            new \DateTimeImmutable()
        );
    }
}
