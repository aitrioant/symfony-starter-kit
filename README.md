# Symfony Backend Starter Kit (DDD / Clean Architecture)

This project is a **Symfony backend starter kit** that I built to explore and demonstrate how to structure a **scalable,
long-lived API** using **Domain-Driven Design (DDD)** and clean architecture principles.

I’ve worked on enough Symfony projects where things start clean and slowly degrade into tightly coupled code. This
repository is my attempt at setting a solid baseline that makes **change cheap** and **architecture visible**.

---

## What this project focuses on

Instead of feature richness, I focused on:

- Clear separation between domain, application and infrastructure
- Explicit use cases instead of generic CRUD
- A domain that does **not** depend on Symfony or Doctrine
- Infrastructure that can be replaced without rewriting business logic
- Making AI integration a normal dependency, not a special case

Some parts may feel slightly over-engineered for the current feature set — that’s intentional.

---

## Architectural approach

The project follows a **hexagonal architecture**:

- The **Domain** contains entities, value objects and business rules
- The **Application layer** coordinates use cases
- The **Infrastructure layer** handles Symfony, Doctrine, HTTP and external services

Dependencies always point inward.

I deliberately avoided putting logic in controllers, entities tied to Doctrine, or framework-specific services inside
the domain.

## Implemented Use Case: User Registration

A complete **User Registration** flow is implemented to serve as a reference:

- Input validation at the application boundary
- Domain-level invariants
- Password hashing via an abstraction
- Persistence through repository interfaces
- No framework dependencies in the domain

This use case illustrates how new features can be added **without modifying existing domain logic**.

--- 

## Example Bounded Context: Notes

To demonstrate extensibility, the project includes a simple **Notes** context.

### Implemented Use Cases

- Create a note
- Update a note
- List notes for a user
- Archive a note (soft delete)

### Why Notes?

This context is intentionally simple but non-trivial:

- Ownership and authorization rules
- Domain validation
- No anemic entities
- Explicit intent in every use case

It shows how **business rules scale naturally** within the architecture.

---

## AI Integration Without Architectural Compromise

AI is integrated as **just another infrastructure dependency**.

### Example: Summarize Notes

- The application layer defines an `AiSummaryService` port
- The infrastructure layer implements it (e.g. OpenAI)
- The domain layer remains completely unaware of AI

This demonstrates:

- Safe integration of non-deterministic services
- Clean dependency direction
- Easy mocking and testing

AI **supports** the domain — it never controls it.

---

## Testing Philosophy

Testing follows architectural boundaries:

### Domain Tests

- Pure unit tests
- No mocks, no framework, no infrastructure
- Fast and deterministic

### Application Tests

- Validate use case behaviour
- Infrastructure replaced by fakes or stubs
- AI mocked at the port level

### Result

Tests focus on **behaviour and intent**, not implementation details — a common weakness in many codebases.

---

## Technology Stack

- **PHP 8+**
- **Symfony**
- **Doctrine ORM** (infrastructure layer)
- **PHPUnit**

---

## Why this repository exists

This project exists mainly as:

- A personal reference
- A discussion piece for technical interviews
- A starting point for real projects

---

## Ideal Use Cases

This project is relevant if you are building:

- API-first platforms
- SaaS backends
- Long-lived Symfony applications
- Systems where correctness and maintainability matter


