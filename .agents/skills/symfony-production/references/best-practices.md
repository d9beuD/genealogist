# Symfony Best Practices (8.1)

Apply the relevant rule; do not restructure a working project wholesale.

## Project & structure
- Create apps with the Symfony binary (`symfony new`); it runs the right Composer command.
- Use the default directory structure (flat, self-explanatory). Don't impose a custom one without reason.

## Configuration
- Use environment variables for infrastructure config (DB URL, mailer DSN) — values that change per machine. Use `.env` files per environment.
- Use Symfony's secrets management for sensitive values (API keys), not plain env vars.
- Use parameters for application behavior config (e.g. notification sender). Put them in `config/services.yaml` (or `services_prod.yaml`). Don't use the Config component unless reused widely with strict validation.
- Prefix your parameter names with `app.` and keep them short (one or two words).
- Use PHP constants for options that rarely change (e.g. items-per-page) — usable in Twig and entities, unlike parameters.

## Business logic & services
- Do NOT create bundles to organize application code; use PHP namespaces. Bundles are for reusable, stand-alone software.
- Use autowiring + autoconfiguration to wire services and apply tags automatically.
- Make services private whenever possible (the default).
- Use YAML to configure your own services.
- Use PHP attributes for Doctrine entity mapping.

## Controllers
- Extend `AbstractController` (it couples to the framework, which is fine for controllers).
- Use dependency injection (constructor / action arguments) to get services — don't pull from the container.
- Use entity value resolvers when convenient to fetch entities from route params.

## Templates
- Use snake_case for template names, directories, and variables.
- Prefix template fragments (partials) with an underscore, e.g. `_form.html.twig`.

## Forms
- Define forms as PHP classes (form types), not inline in controllers.
- Add form buttons in templates, not in the form class.
- Define validation constraints on the underlying object, not the form.
- Use a single action to render AND process the form.

## Internationalization
- Use the XLIFF format for translation files.
- Use keys (e.g. `form.submit`) for translations, not the source content string.

## Security
- Define a single firewall unless you genuinely have two separate auth systems.

## Web assets
- Use AssetMapper to manage web assets.

## Tests
- Smoke-test your URLs early with a data provider asserting `assertResponseIsSuccessful()` (see testing.md).
- Hard-code raw URLs in functional tests (not generated from routes) so route changes fail loudly.

## Done criteria
- Applied the specific rule(s) requested. Stop.
