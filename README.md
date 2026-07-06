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

## Customization

### Custom Credentials

By default, this package uses the `email` and `password` fields as the authentication credentials.

You can customize these credentials by creating a class that implements the `Narakode\FineAuth\Auth\AuthCredentials` interface.

The class should define a `rules` method that returns an array containing the credential fields and their validation rules.

For example, create `App\Auth\AuthCredentials.php`.

```php
<?php

namespace App\Auth;

use Narakode\FineAuth\Auth\AuthCredentials as AuthCredentialsContract;

class AuthCredentials implements AuthCredentialsContract
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
            'role' => ['required', 'in:admin,user'],
        ];
    }
}
```

Then, register the `AuthCredentials` in your application service provider (`App\Providers\AppServiceProvider.php`).

```php
<?php

namespace App\Providers;

use App\Auth\AuthCredentials;
use Illuminate\Support\ServiceProvider;
use Narakode\FineAuth\Auth\AuthCredentials as AuthCredentialsContract;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AuthCredentialsContract::class, AuthCredentials::class);
    }
}
```


### Custom Authenticator

By default, this package uses `Auth::attempt($credentials)` to determine whether the credentials are valid.

You can customize this behavior by creating a class that implements the `Narakode\FineAuth\Auth\Authenticator` interface.

The class should define an `attempt` method that accepts a `credentials` array as its parameter.

The `attempt` method should return a `User` object when the credentials are valid, or `false` when they are invalid.

For example, create `App\Auth\Authenticator.php`.

```php
<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Narakode\FineAuth\Auth\Authenticator as AuthenticatorContract;

class Authenticator implements AuthenticatorContract
{
    public function attempt(array $credentials): User|false
    {
        $user = User::firstWhere('email', $credentials['email']);

        if (!$user) {
            return false;
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return false;
        }

        return $user;
    }
}
```

Then, register the `Authenticator` in your application service provider (`App\Providers\AppServiceProvider.php`).

```php
<?php

namespace App\Providers;

use App\Auth\Authenticator;
use Illuminate\Support\ServiceProvider;
use Narakode\FineAuth\Auth\Authenticator as AuthenticatorContract;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AuthenticatorContract::class, Authenticator::class);
    }
}
```


### Custom Auth Response

You can customize the auth response (when login and refresh token are successful) by creating a class that implements the `Narakode\FineAuth\Auth\AuthResponse` interface.

The class should have a `toArray` method with a `Narakode\FineAuth\Auth\AuthResult` object as its parameter.

The `toArray` method should return an array that will be sent as the JSON response.

The `AuthResult` object exposes the `getUser` method to access the authenticated `User` object and the `getAccessToken` method to access the user's access token.

For example, create `App\Auth\AuthResponse.php`.

```php
<?php

namespace App\Auth;

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
use App\Auth\AuthResponse;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AuthResponseContract::class, AuthResponse::class);
    }
}
```