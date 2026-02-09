# lapp-stack-demo

## **Upgraded README.md**

This repository contains code for a basic REST API which will be built with JWT Authentication, and will be built using the LAPP stack (Linux, Apache, PostgreSQL, PHP). The API will be built using PHP 8.3.30 and will be containerized using Docker. The API will be tested using PHPUnit.

## **Folder Structure:**

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


## **Installation and Usage: Composer After Build**

`docker compose run --rm app composer install`
`docker compose run --rm app composer dump-autoload`
