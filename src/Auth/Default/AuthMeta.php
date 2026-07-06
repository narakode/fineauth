<?php

namespace Narakode\FineAuth\Auth\Default;

use Narakode\FineAuth\Auth\AuthContext;
use Narakode\FineAuth\Auth\AuthMeta as AuthMetaContract;

class AuthMeta implements AuthMetaContract
{
    public function toArray(AuthContext $auth): array
    {
        return [];
    }
}