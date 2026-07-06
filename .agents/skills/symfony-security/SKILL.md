---
name: symfony-security
description: >-
  Use for ALL Symfony 8.1 security work: authentication and authorization, login
  (form_login, json_login, http_basic, login link), firewalls and security.yaml,
  user providers, roles and role_hierarchy, password hashing, access control
  (access_control, #[IsGranted], denyAccessUnlessGranted), voters, CSRF protection,
  logout, and custom authenticators. Trigger whenever a task mentions login,
  secure a route, protect an endpoint, roles, permissions, hashing passwords,
  who can access, firewall, or security.yaml.
---

Configure authentication and authorization in Symfony 8.1 (PHP 8.2+ attributes).

## Use this skill when

- Setting up login (form, JSON API, HTTP basic, login link) or logout.
- Editing `config/packages/security.yaml`: firewalls, providers, password_hashers, access_control, role_hierarchy.
- Restricting access to routes/controllers with roles, `#[IsGranted]`, or expressions.
- Creating a User entity, user provider, or hashing/verifying passwords.
- Writing a Voter for object-level permissions.
- Adding CSRF protection or a custom authenticator.

## Do not use this skill when

- The task is generic routing, controllers, forms, or Doctrine with no security aspect.
- The task is API rate limiting unrelated to login.

## Instructions

Follow in order. Stop as soon as the done criterion for the task is met. Do NOT
add extra firewalls, providers, or config the task did not ask for.

1. Read `config/packages/security.yaml` first. If a required block (provider,
   firewall, password_hasher) already exists, EDIT it; never duplicate or recreate it.
2. Identify the single task type and open the ONE matching reference file (see
   "Reference files"). Do not open more than one unless the task spans areas.
3. Apply the change using exact namespaces from that reference. Use PHP 8.2
   attributes (`#[IsGranted]`, `#[CurrentUser]`, `#[Route]`), not annotations.
4. Roles must start with `ROLE_`. To check roles use `$this->isGranted('ROLE_X')`
   or `#[IsGranted('ROLE_X')]` / `denyAccessUnlessGranted` — never `in_array(...$user->getRoles())`.
5. After editing `security.yaml`, do not reformat unrelated lines. Keep the `dev`
   firewall first and the catch-all firewall (no `pattern`) last.
6. Done criteria — stop here:
   - Authentication: the firewall has one authenticator key (e.g. `form_login`) and a provider. Stop.
   - Authorization: the route/controller is protected by `access_control`, `#[IsGranted]`, or a voter that returns the right decision. Stop.
   - Password: `password_hashers` is set and code uses `UserPasswordHasherInterface::hashPassword()`. Stop.
   - CSRF: `enable_csrf: true` on the authenticator and the token field is in the template. Stop.
7. Anti-loop: do not re-run the same edit. If access control "works" (the right
   users pass, others get 403/redirect to login), the task is complete — return.

## Reference files

- `references/authentication.md` — open when configuring login/logout: firewalls,
  providers, form_login, json_login, http_basic, login throttling, fetching the
  current user, custom authenticators (Passport/UserBadge).
- `references/authorization-voters.md` — open when restricting access: access_control,
  roles, role_hierarchy, `#[IsGranted]`, `denyAccessUnlessGranted`, expressions,
  and writing/configuring Voters.
- `references/passwords-users.md` — open when creating the User class/entity,
  user providers, or hashing/verifying/migrating passwords.
- `references/csrf-firewalls.md` — open when adding CSRF protection or tuning
  firewall structure (patterns, host/IP matching, requires_channel/https).
