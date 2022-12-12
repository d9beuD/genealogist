# Genealogist, a free family tree app

I started this project as a personal challenge. I wanted to deepen my web programing skills. Because I created this repository a long time ago, the project structure changed several times as I was changing my mind on what this project may work or look like.

## Backend usage

In a dev environment, you can start the server with [Symfony CLI](https://symfony.com/download) using the following command.

```sh
symfony server:start
```

## Frontend usage

For the frontend, I use a [Vue 3](https://vuejs.org) application with [Vite](https://vitejs.dev) environment. So you can start the dev server with:

#### `npm`

```sh
npm i
npm run dev
```

#### `yarn`

```sh
yarn
yarn dev
```

### There is a lot of warnings in the console

In this project, I use the [BootstrapVue](https://github.com/bootstrap-vue/bootstrap-vue) library that is not yet fully compatible with Vue 3. It is still writtent with Vue 2. Hopefully, there is a package called `@vue-compat` that provides compatibility between Vue 2 components/library and Vue 3, that facilitate code migration from one version to another. 

So, [BootstrapVue works in this Vue 3 project](https://bootstrap-vue.org/vue3) in counterpart of `@vue-compat` warning us about a lot of deprecated functionalities from Vue 2. You can safely ignore them as we are just wating for BootstrapVue to migrate their library to fully support Vue 3.
