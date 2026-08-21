# Genealogist, a free family tree app

[![Deploy to Coolify](https://github.com/d9beuD/genealogist/actions/workflows/deploy.yml/badge.svg)](https://github.com/d9beuD/genealogist/actions/workflows/deploy.yml)
[![Release Please](https://github.com/d9beuD/genealogist/actions/workflows/release-please.yml/badge.svg)](https://github.com/d9beuD/genealogist/actions/workflows/release-please.yml)

I started this project as a personal challenge. I wanted to deepen my web programing skills. Because I created this repository a long time ago, the project structure changed several times as I was changing my mind on what this project may work or look like.

## Usage

### Build Docker image

Use the repository Makefile for the Docker workflow. It passes `UID` and `GID`
automatically for better WSL and bind-mount permissions:

```sh
make build
```

Common follow-up targets:

```sh
make up
make stop
make exec
make init
```

By default, `make build` builds the `dev` target from the Dockerfile. To build the
production stage instead, use:

```sh
make build-prod
```

The dev app is exposed on `http://localhost:8000` through Compose.

If you need the raw Docker command, pass your current user IDs so files created in the
container stay writable from the host:

```sh
docker build --build-arg UID=$(id -u) --build-arg GID=$(id -g) -t genealogist-backend .
```

### Start dev server

In a dev environment, you can start the server with [Symfony CLI](https://symfony.com/download) using the following command.

```sh
symfony serve -d
```

### Make icons work

Because Font Awesome won't serve icons on a local IP address (`127.0.0.1`), change the Symfony's server provided address with `localhost` and icons will start working again.

### Entities changed

You made modifications to entities? Don't forget to [create and execute](https://symfony.com/doc/current/doctrine.html#migrations-creating-the-database-tables-schema) a migration file.

## I want to contribute

Thank you, any help is appreciated. Go to issues tab and find one you like without a code branch refered. Then, feel free to fork this repository and start a new pull request.

## Releases

This repository uses `release-please` to generate release PRs and GitHub releases from conventional commits on `main`.
When a release PR is merged, `release-please` publishes the GitHub release and the deploy workflow triggers the Coolify deployment webhook on the `release.published` event.
For cleaner generated release notes, prefer meaningful conventional commits or squash-merge mechanical PRs before release.
