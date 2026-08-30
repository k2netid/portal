<?php

declare(strict_types=1);

namespace Modules\Publishing\Contracts;

/**
 * Reader identity for public comments/bookmarks. Never a console User.
 */
final readonly class MemberIdentity
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public bool $emailVerified = false,
    ) {}
}
