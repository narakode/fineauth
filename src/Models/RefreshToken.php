<?php

namespace Narakode\FineAuth\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $fillable = ['token', 'expire_at', 'user_id'];

    public function casts() : array
    {
        return [
            'expire_at' => 'datetime'
        ];
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
