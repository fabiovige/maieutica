<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Illuminate\Support\Str;

readonly class PasswordData
{
    private function __construct(
        public string $plainPassword,
        public string $hashedPassword
    ) {
    }

    public static function generate(int $length = 10): self
    {
        $plainPassword = Str::random($length);
        
        return new self(
            plainPassword: $plainPassword,
            hashedPassword: bcrypt($plainPassword)
        );
    }

    public static function fromPlain(string $plainPassword): self
    {
        return new self(
            plainPassword: $plainPassword,
            hashedPassword: bcrypt($plainPassword)
        );
    }
}