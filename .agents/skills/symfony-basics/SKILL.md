---
name: symfony-basics
description: >-
  Use when building Symfony 8.1 web pages and features: creating routes,
  controllers, Twig templates, Doctrine entities and CRUD, forms, and
  validation. Reach for this skill whenever you create a page, wire a URL to a
  controller, render HTML with Twig, persist or query data with Doctrine
  entities/repositories, build a Symfony form, or add validation constraints.
---

Build core Symfony 8.1 features (routing, controllers, Twig, Doctrine, forms, validation) using PHP 8.2+ attributes, constructor injection, and thin controllers.

## Use this skill when

- Creating a new page (route + controller + template).
- Adding or editing a `#[Route]`, controller action, or Twig template.
- Creating a Doctrine entity, repository, or CRUD; persisting/querying data.
- Building a Symfony form (`AbstractType`, `handleRequest`).
- Adding validation constraints or a custom constraint.

## Do not use this skill when

- The task is security/auth (login, voters, firewalls) — use the security skill.
- The task is project setup, deployment, caching, messenger, or console commands beyond `make:`/`debug:` — use the relevant skill.
- The file/feature already exists and works — edit it, do not recreate it.

## Instructions

Follow these steps in order. Do the minimum needed; stop when the requested feature works.

1. Identify the task type and open EXACTLY ONE reference file from the list below. Do not open more than one unless the task spans areas.
2. Check if the target file already exists (`src/Controller/`, `src/Entity/`, `src/Form/`, `templates/`). If it exists, edit it; never recreate from scratch.
3. Write code using PHP 8.2+ attributes (`#[Route]`, `#[ORM\Column]`, `#[Assert\...]`), constructor injection for services, and keep controllers thin (delegate logic to services/repositories).
4. Use exact namespaces from the reference file. Do not invent class names or attribute arguments.
5. For database schema changes, generate a migration: `php bin/console make:migration` then `php bin/console doctrine:migrations:migrate`. Do not hand-edit the database.
6. Verify routes with `php bin/console debug:router` when relevant. Stop here.

Anti-loop rules:
- ONE reference file per task. If unsure which, pick the closest match and proceed.
- Do not regenerate files that already exist; do not re-run a passing command.
- If a `make:` command already created files, edit those files — do not duplicate them.
- Stop as soon as the requested feature is implemented. Do not add unrequested fields, routes, or tests.

## Reference files

- `references/routing.md` — open when defining or debugging routes: `#[Route]`, route names, HTTP methods, `{param}` placeholders, requirements, URL generation.
- `references/controllers.md` — open when writing controller actions: `AbstractController`, Response/JSON, redirects, service injection, request reading, 404s, `EntityValueResolver`/`#[MapEntity]`.
- `references/templates.md` — open when rendering HTML: Twig syntax, `render()`, variables, `path()`, template inheritance, rendering a form.
- `references/doctrine.md` — open when working with entities/database: entity mapping, persist/flush, repositories, queries, associations, CRUD generation.
- `references/forms-validation.md` — open when building forms or adding validation: form types, `handleRequest`, rendering, constraints, validation groups, custom constraints.
