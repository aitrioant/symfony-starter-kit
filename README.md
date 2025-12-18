# Symfony Backend Starter Kit — Scalable Architecture with DDD & AI

This repository is a **production-ready backend starter kit** built with **Symfony**, designed to demonstrate how to
structure **scalable, maintainable APIs** using **Domain-Driven Design (DDD)** and **clean architecture principles**.

Rather than focusing on features, this project focuses on **engineering quality**: clear boundaries, explicit use cases,
testability, and safe integration of external services such as AI.

---

## 👨‍💻 What This Project Demonstrates

This codebase intentionally showcases skills that are relevant for **mid to senior backend roles**:

- Designing **clean, modular architectures**
- Applying **DDD in a pragmatic way**
- Avoiding framework-driven domain models
- Writing **use-case-oriented code** instead of generic CRUD
- Integrating **AI services without leaking infrastructure concerns**
- Building systems that are **easy to test and evolve**

---

## 🧠 Architectural Decisions (Why This Matters)

### Domain-Driven Design (DDD)

- Business logic is isolated from frameworks and infrastructure
- Entities and value objects enforce invariants
- Application services express business use cases explicitly

### Hexagonal Architecture

- Dependencies point inward
- Infrastructure is replaceable (database, AI provider, hashing, etc.)
- Clear separation between **what the system does** and **how it does it**

### Explicit Use Cases

Instead of:

Controller → Entity → Repository

The project follows:

Controller → Use Case → Domain → Port → Adapter

This results in code that is:

- Easier to reason about
- Safer to change
- More aligned with real business workflows

---

## 🔐 Implemented Use Case: User Registration

A complete **User Registration** flow is implemented to serve as a reference:

- Input validation at the application boundary
- Domain-level invariants
- Password hashing via an abstraction
- Persistence through repository interfaces
- No framework dependencies in the domain

This use case illustrates how new features can be added **without modifying existing domain logic**.

---

## 🗂 Example Bounded Context: Notes

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

## 🤖 AI Integration Without Architectural Compromise

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

## 🧪 Testing Philosophy

Testing follows architectural boundaries:

### Domain Tests

- Pure unit tests
- No mocks, no framework, no infrastructure
- Fast and deterministic

### Application Tests

- Validate use case behavior
- Infrastructure replaced by fakes or stubs
- AI mocked at the port level

### Result

Tests focus on **behavior and intent**, not implementation details — a common weakness in many codebases.

---

## 📦 Technology Stack

- **PHP 8+**
- **Symfony**
- **Doctrine ORM** (infrastructure layer)
- **PHPUnit**
- **Hexagonal Architecture**
- **AI provider via adapter (e.g. OpenAI)**

---

## 🚀 Why This Project Exists

Many Symfony projects start clean but degrade over time due to:

- Tight coupling
- Framework-driven design
- Unclear business boundaries

This starter kit demonstrates how to:

- Keep complexity under control
- Add features without fear
- Integrate modern tools (AI) responsibly
- Build backends that age well

---

## 🎯 Ideal Use Cases

This project is relevant if you are building:

- API-first platforms
- SaaS backends
- Long-lived Symfony applications
- Systems where correctness and maintainability matter


