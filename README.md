# Genealogist, a free family tree app

I started this project as a personal challenge. I wanted to deepen my web programing skills. Because I created this repository a long time ago, the project structure changed several times as I was changing my mind on what this project may work or look like.

## Usage

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
