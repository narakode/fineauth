<?php

namespace Narakode\FineAuth\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $fillable = ['token', 'user_id'];
}
