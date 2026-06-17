---
trigger: always_on
---

# Integer - Agent Instructions

# Project Philosophy

You are working on an existing production Laravel application.

Your primary goal is to preserve the project's architecture, coding standards, maintainability and consistency.

Never redesign the project when the existing architecture already solves the problem.

Always behave as if you are joining an experienced development team.

---

# General Rules

- Always understand the project before making changes.
- Always analyze the existing architecture.
- Always follow the current coding style.
- Never invent new architectural patterns.
- Reuse existing project conventions whenever possible.
- Keep naming conventions consistent.
- Minimize the scope of every change.
- Write clean, readable and maintainable code.
- Avoid unnecessary complexity.
- Do not introduce new libraries or packages unless explicitly requested.

---

# Before Coding

Before writing any code, always:

1. Analyze the project structure.
2. Search for similar implementations.
3. Read the related Models, Controllers, Services, Requests, Policies and Views.
4. Understand how the requested feature fits into the current architecture.
5. Explain your implementation plan.
6. List every new file that will be created.
7. List every existing file that will be modified.
8. Wait for approval before making any changes.

Never start implementing immediately.

---

# Existing Architecture

Always identify the module that is most similar to the requested feature.

Use it as the implementation reference.

Mirror:

- Folder organization
- Controller structure
- Model organization
- Service layer
- Validation
- Route organization
- Blade views
- Components
- Naming conventions
- Coding style

Never create a different architecture when an existing pattern already exists.

---

# New Features

When implementing a new feature or module:

- Create new files only when they represent new functionality.
- Follow the existing folder structure exactly.
- Follow the project's architecture.
- Keep the feature modular.
- Reuse shared services.
- Reuse helpers.
- Reuse traits.
- Reuse existing abstractions whenever possible.

Modify existing modules only when integration requires it.

Avoid unnecessary changes.

---

# Laravel Rules

Always follow the Laravel conventions already adopted by the project.

Reuse existing:

- Eloquent models
- Relationships
- Form Requests
- Services
- Policies
- Middleware
- Blade layouts
- Components
- Route organization
- Resource Controllers
- Traits
- Helpers

Never duplicate business logic.

---

# Database

Never modify existing database tables unless explicitly requested.

Prefer:

- New migrations
- Foreign keys
- Existing naming conventions
- Existing relationships

Always preserve backwards compatibility.

Never remove columns without approval.

---

# UI Rules

When working with Blade:

- Reuse layouts.
- Reuse components.
- Reuse partials.
- Follow existing Bootstrap conventions.
- Keep spacing consistent.
- Preserve the existing visual identity.

Do not redesign the interface unless requested.

---

# Code Quality

Write code that is:

- Readable
- Maintainable
- Consistent
- Simple
- Well organized

Avoid:

- Duplicate code
- Dead code
- Overengineering
- Unnecessary abstractions
- Large refactors
- Breaking changes

Only change what is necessary.

---

# Safety

Never:

- Delete files without permission.
- Rename folders without permission.
- Perform massive refactors.
- Replace existing architecture.
- Modify unrelated modules.
- Introduce breaking changes.

Always explain why a change is necessary.

Always prefer incremental changes.

---

# Documentation

When implementing a complex feature:

- Explain the architecture.
- Explain important decisions.
- Keep comments concise.
- Preserve existing documentation.

---

# When Unsure

If multiple implementation options exist:

- Explain the alternatives.
- Recommend the option that best matches the current architecture.
- Wait for confirmation before proceeding.

---

# Golden Rule

The Integer already has an established architecture.

Your job is NOT to reinvent the project.

Your job is to understand the existing codebase, respect its architecture and extend it consistently.

Every new feature should look as if it had always been part of the project.