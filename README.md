# lapp-stack-demo

## **Upgraded README.md**

This repository contains code for a Support Desk Demonstration for managing corporate IT support tickets. It is built using the LAPP stack (Linux, Apache, PostgreSQL, PHP) and includes features such as REST API, ticket creation, and ticket management.

### **Features:**

| Method | Route               | Description      |
| ------ | ------------------- | ---------------- |
| GET    | `/api/tickets/`     | list all tickets |
| GET    | `/api/tickets/{id}` | get one ticket   |
| POST   | `/api/tickets/`     | create ticket    |
| PUT    | `/api/tickets/{id}` | update ticket    |
| DELETE | `/api/tickets/{id}` | delete ticket    |

## **Folder Structure:**

lapp-stack-demo/
├── docker/
│   └── 000-default.conf
├── Dockerfile
├── docker-compose.yml
├── init-prod/
│   └── init.sql
└── html/
    ├── composer.json
    ├── .htaccess
    ├── src/
    │   └── SupportDesk/
    │       ├── TicketRepository.php
    │       └── Response.php
    ├── tests/
    │   └── SupportDesk/
    │       └── TicketRepositoryTest.php
    └── api/
        └── tickets/
            ├── index.php
            └── id/
                └── index.php

## **Installation and Usage: Composer After Build**

`docker compose run --rm app composer install`
`docker compose run --rm app composer dump-autoload`
