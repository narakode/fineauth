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

## Usage

Register the authentication routes in your routes file (for example, `routes/api.php`):

```php
use Narakode\FineAuth\FineAuth;

FineAuth::routes();
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
