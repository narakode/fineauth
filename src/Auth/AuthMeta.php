<?php

namespace Narakode\FineAuth\Auth;

interface AuthMeta
{
    public function toArray(AuthContext $auth): array;
}