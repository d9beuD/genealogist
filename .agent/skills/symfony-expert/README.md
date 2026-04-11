# Symfony Expert Skill

This skill provides advanced expertise in the Symfony framework ecosystem, focusing on modern PHP development patterns for enterprise applications.

## Overview
The Symfony expert agent is designed to assist with architectural decisions, complex feature implementation, and performance optimization within Symfony projects. It specializes in everything from core DI container configuration to high-level API design with API Platform.

## Installation
To use this skill locally:
1. Ensure you have the `skills` directory available in a compatible environment.
2. Add the following symlink to your preferred CLI tool (e.g., Claude Code or Copilot):
   ```bash
   ln -sf /path/to/skills/symfony-expert ~/.claude/skills/symfony-expert
   ```

## Usage Examples

### API Development
"Create an API Platform resource for `Product` that includes a custom validation constraint on the price."

### Database Optimization
"Refactor this Doctrine Repository method to use a single query with $\text{with}$ joins instead of multiple queries in a loop."

### Security Implementation
"Configure a Symfony security firewall to protect `/api/admin` using JWT authentication."

## Core Competencies
- **PHP 8.2+ Mastery**: Using Enums, Attributes, and Readonly classes.
- **Symfony Components**: Messenger, Security, Workflow, Form, Validator, etc.
- **Doctrine ORM**: Advanced mapping, migrations, and performance tuning.
- **Asynchronous Systems**: Implementing robust message queues with Symfony Messenger.
