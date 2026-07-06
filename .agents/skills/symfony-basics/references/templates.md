# Twig Templates (Symfony 8.1)

Templates live in `templates/` and use Twig. Install: `composer require symfony/twig-bundle`.

## Syntax

- `{{ ... }}` print a variable or expression.
- `{% ... %}` logic (if, for, etc.).
- `{# ... #}` comment.

```twig
{# templates/user/notifications.html.twig #}
<h1>Hello {{ user_first_name }}!</h1>
<p>You have {{ notifications|length }} new notifications.</p>
```

## Rendering from a controller

```php
return $this->render('user/notifications.html.twig', [
    'user_first_name' => $userFirstName,
    'notifications' => $userNotifications,
]);
```

Use snake_case for filenames/variables. Filenames carry two extensions: `<name>.<format>.twig` (e.g. `index.html.twig`).

## Accessing data

`{{ user.name }}` works whether `user` is an array key, public property, or getter (`getName()`/`isName()`/`hasName()`), in that resolution order.

## Linking to pages

Use `path()` with the route name and parameters (never hardcode URLs):

```twig
<a href="{{ path('blog_index') }}">Homepage</a>
<a href="{{ path('blog_post', {slug: post.slug}) }}">{{ post.title }}</a>
```

`url()` generates an absolute URL.

## Filters and functions

```twig
{{ title|upper }}
{{ comment.publishedAt|date('Y-m-d') }}
```

## Loops and conditionals

```twig
{% for post in blog_posts %}
    <li>{{ post.title }}</li>
{% else %}
    <li>No posts yet.</li>
{% endfor %}

{% if user.isLoggedIn %}
    Hello {{ user.name }}!
{% endif %}
```

## Template inheritance

```twig
{# templates/base.html.twig #}
<!DOCTYPE html>
<html>
    <body>
        {% block body %}{% endblock %}
    </body>
</html>
```

```twig
{# templates/blog/index.html.twig #}
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Blog</h1>
{% endblock %}
```

## Rendering a form

Pass the form from the controller and render it (see forms-validation.md):

```twig
{{ form_start(form) }}
    {{ form_widget(form) }}
{{ form_end(form) }}
```

Or render the whole form at once: `{{ form(form) }}`.
