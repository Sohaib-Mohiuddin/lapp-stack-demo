# lapp-stack-demo

## **Upgraded README.md**

This repository contains code for a basic REST API which will be built with JWT Authentication, and will be built using the LAPP stack (Linux, Apache, PostgreSQL, PHP). The API will be built using PHP 8.3.30 and will be containerized using Docker. The API will be tested using PHPUnit.

## **Folder Structure:**

```
lapp-stack-demo/
├── codebase/
│   ├── api/
│   │   └── products/
│   │       ├── index.php
│   │       └── id/
│   │           └── index.php
│   ├── src/
│   │   ├── ProductManager.php
│   │   └── Response.php
│   ├── tests/
│   │   └── ProductManagerTest.php
│   ├── composer.json
│   └── .htaccess
├── docker/
│   └── 000-default.conf
├── init-prod/
│   └── init.sql
├── Dockerfile
├── docker-compose.yml
└── .env
```

## **Installation and Usage: Composer After Build**

`docker compose run --rm app composer install`
`docker compose run --rm app composer dump-autoload`

## **JWT Tokens**

The API uses JWT tokens for authentication. The tokens are generated using the `verifyBearerOrFail` function in the `AuthClient` class. The tokens are signed using a secret key which is stored in the `.env` file.

- Send POST request to `http://localhost:3000/auth/login` with JSON body:
```json
{
    "username": "ENTER_USERNAME_HERE",
    "password": "ENTER_PASSWORD_HERE"
}
```
- The response will contain a JWT token which can be used for authentication in subsequent requests.
- Copy the `access_token` from the response and include it in the `Authorization` header of your requests as follows:
    - `Authorization: Bearer access_token_here`
    - http://localhost:8080/api/products



