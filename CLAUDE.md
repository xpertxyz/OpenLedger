# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repo is

A **design handoff bundle**, not an application. There is no build, test, lint, package manager, or backend here — nothing to run except opening the HTML in a browser.

- `Home Expense Tracker.dc.html` — interactive prototype in a proprietary component-streaming format (`<x-dc>`, `<sc-if>`, `{{ ... }}` bindings). It references `./support.js` and `_ds/organic-.../{styles.css,_ds_bundle.js}` which are **not vendored in this repo** — the file only renders inside the design tool that produced it. Treat it as a **spec to read**, not code to execute or edit.
- `design-tokens/styles.css` — the real, standalone stylesheet. This is the source of truth for every color/type/spacing/radius/shadow value and for the `.btn / .card / .tag / .dialog / .input / .field / .seg` component classes the production app must use.
- `design-tokens/organic-design-system-guide.md` — written rules for the "Organic" design system (usage do's/don'ts, ramp semantics, interaction states).
- `README.md` — the handoff brief: screens, behaviors, data model, and the recommended production stack.

## When the user asks to "build the app"

The intended output is a **new project in a real stack** that reproduces the prototype. Per README.md:

- Recommended: **PHP + SQLite** (no framework or Slim), zero build step, drop-in on shared hosting.
- Alternative: **Node + Express + better-sqlite3**, only if the host reliably keeps a persistent process alive.
- **Skip WordPress.** The README rejects it explicitly.
- Server-render the four tabs, or ship a small React/Vue bundle if client state is preferred. Move all state out of `localStorage` into the tables listed in README.md § State Management.

The production app must **link `design-tokens/styles.css` verbatim** (or port its `:root` tokens into the target theme system) and consume `var(--color-*)`, `var(--font-*)`, `var(--space-*)`, `var(--radius-*)`, `var(--shadow-*)` rather than hard-coding values. This is a hard rule from the design-system guide.

## Non-obvious behavior rules

Two rules in README.md are easy to miss and will be gotten wrong by default:

1. **Recurring → expense auto-posting.** A recurring item does *not* create an expense when created/edited. It only posts once `next_date` has actually passed, then loops forward through any missed periods. In production this must be a **server-side scheduled job** (cron / queue worker, at least daily), not a client tick.
2. **Credit-card bills are ordinary expenses.** No separate statement-import entity; the household logs the bill as one expense on the day it's paid. Don't invent a statement-reconciliation feature.

Also: Save buttons must be **disabled** until valid (amount > 0, non-empty names) — not just no-op. Every list row deletes immediately on trash click (no confirm) in the prototype; adding a confirm dialog for destructive actions is acceptable.

## Editing the design tokens

If asked to change the look, edit the `:root` variables at the top of `design-tokens/styles.css` — the tonal ramps (`--color-accent-100..900`, `--color-accent-2-100..900`, `--color-neutral-100..900`) are generated in OKLCH on one shared lightness scale, so preserve that relationship when adjusting: don't tweak a single step in isolation.
