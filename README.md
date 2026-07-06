# Fine Auth

A simple authentication package for Laravel REST APIs.

## Features

* Login
* Access Tokens
* Refresh Tokens
* Current Authenticated User

## Requirements

* PHP ^8.3
* Laravel ^13
* Laravel Sanctum ^4.3

## Installation

Install the package:

```bash
composer require narakode/fineauth
```

Publish the package configuration:

```bash
php artisan vendor:publish --provider="Narakode\FineAuth\FineAuthServiceProvider"
```

This package requires Laravel Sanctum. If you haven't installed it yet, run:

```bash
php artisan install:api
```

Add the `HasApiTokens` and `HasRefreshTokens` traits to your `User` model.

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Narakode\FineAuth\HasRefreshTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasRefreshTokens;
}
```

## Usage

Register the authentication routes in your routes file (for example, `routes/api.php`):

```php
use Narakode\FineAuth\FineAuth;

FineAuth::routes();

# Custom attributes

FineAuth::routes([
    'as' => 'test.',
    'prefix' => 'test/'
]);
```

You can verify that the routes have been registered by listing your application's routes:

```bash
php artisan route:list

# Example:
# POST  api/login
```

## Endpoints

### Login

The `POST /login` endpoint requires the following request parameters:

* `email`
* `password`

The package uses Laravel's authentication system to validate the provided credentials.

If the credentials are invalid, it returns a **401 Unauthorized** response:

```json
{
    "message": "The provided credentials do not match our records."
}
```

If authentication succeeds, it returns a **200 OK** response:

```json
{
    "access_token": "xxxx",
    "user": {
        "id": "xxx",
        "name": "xxx",
        "email": "xxx"
    }
}
```

A refresh token is also returned as an HTTP cookie named `refresh_token` with the following attributes:

* Expires in 1 hour
* `HttpOnly`
* `Secure`
* `SameSite`

### Current User

The `GET /me` endpoint returns the authenticated user associated with the provided access token.

Provide the access token in the `Authorization` header using the following format:

```
Authorization: Bearer <access_token>
```

If the access token is valid, the response will be:

```json
{
    "user": {
        "id": "xxx",
        "name": "xxx",
        "email": "xxx"
    }
}
```

If the access token is missing or invalid, the endpoint returns a `401 Unauthorized` response.

### Refresh Token

The `POST /refresh-token` endpoint returns a new access token and the authenticated user using the refresh token stored in a cookie.

If the cookie is exists and has not expired, the response is the same as login. Otherwise, the endpoint returns `401 Unauthorized`.

## Custom Auth Response

You can customize the auth response (when login and refresh token are successful) by creating a class that implements the `Narakode\FineAuth\Auth\AuthResponse` interface.

The class should have a `toArray` method with a `Narakode\FineAuth\Auth\AuthResult` object as its parameter.

The `toArray` method should return an array that will be sent as the JSON response.

The `AuthResult` object exposes the `getUser` method to access the authenticated `User` object and the `getAccessToken` method to access the user's access token.

For example, create `App\Responses\AuthResponse.php`.

```php
<?php

namespace App\Responses;

use Narakode\FineAuth\Auth\AuthResponse as AuthResponseContract;
use Narakode\FineAuth\Auth\AuthResult;

class AuthResponse implements AuthResponseContract
{
    public function toArray(AuthResult $auth): array
    {
        return [
            'user' => $auth->getUser(),
            'access_token' => $auth->getAccessToken(),
            'roles' => [],
            'permissions' => []
        ];
    }
}
```

Then, register the `AuthResponse` in the app service provider (`App\Providers\AppServiceProvider.php`).

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Narakode\FineAuth\Auth\AuthResponse as AuthResponseContract;
use App\Responses\AuthResponse;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AuthResponseContract::class, AuthResponse::class);
    }
}
```