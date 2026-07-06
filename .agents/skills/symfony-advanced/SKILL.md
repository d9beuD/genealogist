---
name: symfony-advanced
description: >-
  Implement advanced Symfony 8.1 components: async/queues with Messenger
  (#[AsMessageHandler], transports, retry, failed messages), Serializer
  (serialize/deserialize, groups, context), Workflow & state machines
  (places/transitions, guards, transition listeners), Mercure realtime
  updates, Lock (mutexes/critical sections), HTTP Client
  (HttpClientInterface), Cache pools (CacheInterface, tags), Rate Limiter
  (RateLimiterFactoryInterface), Scheduler (#[AsSchedule], RecurringMessage),
  and Webhook/RemoteEvent consumers. Use whenever a task mentions queues,
  background jobs, async processing, message bus, serialization to JSON,
  state machines, realtime/SSE, distributed locks, calling external APIs,
  caching, throttling/rate limiting, cron/recurring tasks, or incoming
  webhooks.
---

Router skill for advanced Symfony 8.1 components. Pick ONE task, open ONE reference file, implement, stop.

## Use this skill when
- Sending work to a queue / processing jobs async (Messenger).
- Serializing objects to JSON/XML/CSV or deserializing requests (Serializer).
- Modeling a lifecycle with places and transitions (Workflow / state machine).
- Pushing realtime updates to browsers (Mercure / SSE).
- Preventing concurrent execution of a critical section (Lock).
- Calling an external HTTP API (HTTP Client).
- Caching computed data with a pool (Cache).
- Throttling requests or actions (Rate Limiter).
- Running recurring / cron-like tasks (Scheduler).
- Receiving and consuming inbound webhooks (Webhook / RemoteEvent).

## Do not use this skill when
- Building controllers, routing, templates, forms, validation, or basic services (use the basics skill).
- Configuring security / authentication (use the security skill).
- Setting up Doctrine entities and migrations (use the architecture skill).

## Instructions
1. Identify the SINGLE component the task needs from the table below. Do not combine components in one pass.
2. Open EXACTLY ONE reference file for that component. Do not open others until the first is done.
3. Check whether config already exists before creating it:
   - `config/packages/messenger.yaml`, `workflow.yaml`, `mercure.yaml`, `webhook.yaml`, `framework.yaml` (rate_limiter/lock/cache nodes).
   - If the relevant config key exists, REUSE it. Do NOT recreate or duplicate it.
4. Add classes/attributes exactly as shown in the reference (correct namespaces, PHP 8.2+ attributes).
5. Stop when the done criterion is met:
   - Messenger: handler invoked (or `messenger:consume <transport>` processes a dispatched message).
   - Serializer: `serialize()`/`deserialize()` returns expected output.
   - Workflow: `$workflow->apply()` / `can()` behaves per transitions.
   - Mercure: `$hub->publish()` returns without error.
   - Lock: `acquire()` returns true and `release()` runs.
   - HTTP Client: `$response->getStatusCode()` / `toArray()` returns data.
   - Cache: `$pool->get()` callback runs once then is cached.
   - Rate Limiter: `consume()->isAccepted()` returns expected bool.
   - Scheduler: provider returns a `Schedule`; transport `scheduler_<name>` consumable.
   - Webhook: parser returns a `RemoteEvent` and consumer is invoked.
6. Do NOT keep editing after the done criterion passes. Do NOT add extra components, tests, or docs unless asked.

## Reference files

| Task | Open file |
| --- | --- |
| Async/queues, message bus, handlers, transports, retry, failed messages | `references/messenger.md` |
| Serialize/deserialize, groups, context, name converters, ignore | `references/serializer.md` |
| Workflow, state machine, places, transitions, guards, listeners | `references/workflow.md` |
| Call external APIs, cache pools, HTTP responses, cache tags | `references/http-client-cache.md` |
| Rate limiting/throttling, distributed locks, critical sections | `references/rate-limiter-lock.md` |
| Recurring/cron tasks, realtime (Mercure/SSE), inbound webhooks | `references/scheduler-mercure-webhook.md` |

- Open `references/messenger.md` when the task is queues, background jobs, async, or the message bus.
- Open `references/serializer.md` when converting objects to/from JSON, XML, or CSV.
- Open `references/workflow.md` when modeling states and transitions.
- Open `references/http-client-cache.md` when calling an external API OR caching data.
- Open `references/rate-limiter-lock.md` when throttling OR locking a critical section.
- Open `references/scheduler-mercure-webhook.md` when scheduling tasks, pushing realtime updates, or handling inbound webhooks.
