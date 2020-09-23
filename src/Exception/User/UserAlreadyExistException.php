<?php

declare(strict_types=1);

namespace App\Exception\User;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use function sprintf;

class UserAlreadyExistException extends ConflictHttpException
{
    private const MESSAGE = 'User with email %s already exist';

    public static function fromEmail(string $email): self
    {
        throw new self(sprintf(self::MESSAGE, $email));
    }
}
