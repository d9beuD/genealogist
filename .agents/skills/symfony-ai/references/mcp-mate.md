# MCP Bundle + Mate

Two distinct things:
- MCP Bundle: expose YOUR Symfony app as an MCP server (or consume one) using the official MCP SDK.
- Mate: a dev-only MCP server that gives your AI assistant knowledge about your PHP app.

## MCP Bundle

Install:

```terminal
composer require symfony/mcp-bundle
```

Routing (required):

```yaml
# config/routes.yaml
mcp:
    resource: .
    type: mcp
```

### Define capabilities (auto-discovered via attributes)

Tools (`Mcp\Capability\Attribute\McpTool`):

```php
use Mcp\Capability\Attribute\McpTool;

class CurrentTimeTool
{
    #[McpTool(name: 'current-time')]
    public function getCurrentTime(string $format = 'Y-m-d H:i:s'): string
    {
        return (new \DateTime('now', new \DateTimeZone('UTC')))->format($format);
    }
}
```

Prompts (`#[McpPrompt(name: ...)]`) return an array of role/content messages.
Resources (`#[McpResource(uri: 'time://current', name: 'current-time')]`) return static data.
(Resource templates exist but are not yet functional — the SDK lacks handlers.)

### Config (config/packages/mcp.yaml)

```yaml
mcp:
    discovery:
        scan_dirs: ['src/Mcp']        # limit attribute scanning
    client_transports:
        stdio: true                   # expose via STDIO command
        http: true                    # expose via HTTP controller
    http:
        path: /_mcp                   # default endpoint
        session:
            store: file               # file | memory | cache | framework
            ttl: 3600
    # 'servers:' (acting as client) is documented but NOT supported yet.
```

## Mate (dev MCP server)

Install (dev only):

```terminal
composer require --dev symfony/ai-mate
```

Initialize, then refresh the autoloader:

```terminal
vendor/bin/mate init
composer dump-autoload
```

`mate init` creates: `mate/` config dir, `mate/src` for extensions,
`mate/AGENT_INSTRUCTIONS.md`, `mcp.json` (for clients like Claude Desktop), and `bin/codex` wrappers.
It also adds `Mate\` PSR-4 autoload-dev and an `extra.ai-mate` block to `composer.json`.

The `symfony/ai-mate-composer-plugin` runs `vendor/bin/mate discover --composer` after
composer install/update to refresh discovered extensions. Mate is framework-agnostic with
Symfony-specific tools via bridges. Development/debugging only — never deploy to production.

Done: MCP tool is discovered/exposed, or Mate server initialized. Stop.
