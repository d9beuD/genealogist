---
name: symfony-ai
description: >-
  Use for ANY Symfony AI task: integrating LLMs/AI into a Symfony or PHP app via
  the Symfony AI components. Covers the Platform component (unified interface to
  OpenAI, Anthropic Claude, Google Gemini, Azure, Mistral, Ollama), building an
  AI Agent, tool calling with the #[AsTool] attribute, Retrieval Augmented
  Generation (RAG) with a vector Store and embeddings, the Chat component and
  conversation memory, configuring everything through the AI Bundle YAML
  (config/packages/ai.yaml), and exposing or consuming an MCP server (MCP Bundle
  / Mate). Trigger on symfony/ai, ai-bundle, AsTool, MessageBag, vectorizer,
  SimilaritySearch, McpTool, or any "add AI/chatbot/embeddings/RAG to Symfony".
---

Integrate AI into Symfony/PHP using the Symfony AI components: Platform, Agent, Store, Chat, AI Bundle, MCP Bundle, Mate.

## Use this skill when

- Adding an LLM call, chatbot, or AI agent to a Symfony or PHP application.
- Wiring a Platform/provider (OpenAI, Anthropic, Gemini, Azure, Mistral, Ollama).
- Creating tools the LLM can call (`#[AsTool]`) or doing tool calling.
- Building RAG: indexing documents, vector Store, embeddings, similarity search.
- Storing conversation history or adding memory (Chat component).
- Configuring any of the above in `config/packages/ai.yaml` (AI Bundle).
- Exposing your app as an MCP server, or using Mate as a dev MCP server.

## Do not use this skill when

- The task is generic Symfony (routing, Doctrine, forms) with no AI part.
- The user wants a non-PHP AI SDK (Python, JS). This skill is PHP/Symfony only.

## Instructions

Follow these steps in order. Do not skip ahead. Do not open more than one reference file per task.

1. Identify the single task category from this router:
   - Provider setup / `$platform->invoke()` / embeddings vectors → `platform.md`
   - Agent + tools / `#[AsTool]` / tool calling / agent memory → `agent-tools.md`
   - RAG / indexing / vector Store / similarity search / retriever → `store-rag.md`
   - `config/packages/ai.yaml` / wiring agents, stores, vectorizers via DI → `ai-bundle.md`
   - MCP server/client / `#[McpTool]` / Mate dev server → `mcp-mate.md`
   - End-to-end recipe (chatbot w/ memory, multi-agent, human-in-the-loop) → `cookbook.md`
2. Open EXACTLY ONE reference file, the one named in step 1. Do not open others.
3. If no component is installed yet, run the install command from that reference
   (always `composer require symfony/ai-*`). Add the AI Bundle only for Symfony apps.
4. Copy the closest example from the reference. Replace only the placeholders
   (api keys via `%env(...)%`, model names, class names, prompts). Do not invent
   class names, namespaces, or YAML keys that are not in the reference.
5. Stop as soon as the code compiles / the YAML is valid and the agent responds.
   Do not add extra providers, tools, or config the user did not ask for.

### Anti-loop rules (mandatory)

- One reference file per task. If you opened the wrong one, close it and open the right one — never read all of them.
- All public namespaces are under `Symfony\AI\...`. If you cannot find a class in the open reference, it is out of scope — omit it, do not guess.
- Done criteria: the requested feature works (agent returns a result, document is indexed, MCP tool is exposed). When met, STOP and report.
- If a config key or class is not in the reference, do NOT search the web or invent it. State it is unknown and stop.
- Do not re-run a working step "to be sure". One correct pass is final.

## Reference files

- `references/platform.md` — open when setting up a provider (OpenAI/Anthropic/Gemini/Azure/Mistral/Ollama), calling `$platform->invoke()`, building MessageBag, or generating embedding vectors.
- `references/agent-tools.md` — open when creating an `Agent`, registering tools with `#[AsTool]`, configuring tool-call limits, structured tool parameters with `#[Schema]`, or adding agent memory.
- `references/store-rag.md` — open when implementing RAG: indexers, vectorizer, vector stores, the Retriever, and the `SimilaritySearch` tool.
- `references/ai-bundle.md` — open when configuring `config/packages/ai.yaml`: platforms, agents, stores, vectorizers, indexers, and store DI aliases.
- `references/mcp-mate.md` — open when exposing your app as an MCP server (`#[McpTool]`, MCP Bundle config) or using the Mate dev MCP server.
- `references/cookbook.md` — open when building a full recipe: chatbot with memory, multi-agent orchestration/handoff, or human-in-the-loop tool approval.
