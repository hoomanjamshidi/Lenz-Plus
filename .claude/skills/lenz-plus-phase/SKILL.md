---
name: lenz-plus-phase
description: Start, resume or finish a phase of the Lenz Plus plugin roadmap. Use whenever the user asks to start/continue/finish a phase ("فاز بعد", "ادامه بده", "phase 3"), when a new session begins work on Lenz Plus, or before committing a phase. Covers reading the roadmap, using the graphify graph, working task by task, the Definition of done (lint, minify, browser check, translations, docs) and the per-phase commit.
---

# Lenz Plus: run a phase

The project is built phase by phase so a session that hits a usage limit can be resumed exactly where it stopped. `docs/ROADMAP.md` is the single source of truth for progress.

## 1. Orient (always, before any code)

1. Read `docs/ROADMAP.md`. Find **Current phase**, its unticked tasks, any `[~]` task and the **Notes** section.
2. Check the working tree: `git status --short` and `git log --oneline -5`. Uncommitted changes mean the last session stopped mid-phase: read them (`git diff --stat`, then the files) before continuing, and never discard them.
3. Re-read the phase's section in `CLAUDE.md` (Module contracts) and, if it ports Studiare code, the `port-from-studiare` skill; if it builds widgets from the designs, the `design-to-elementor` skill.
4. Ask the graph before reading files: `graphify query "<what you need>" --budget 1500` (e.g. "how does Studiare Library install presets", "Lenz mobile menu open"). Then read only the files it points to.
5. If the local site is needed, start it in the background: `php -d memory_limit=1024M -S 127.0.0.1:8888 -t .dev/wp` (see CLAUDE.md → Testing).

## 2. Work task by task

- Do the roadmap tasks in order. One task = one coherent unit (a class with its view and assets, a widget with its CSS, a preset).
- As soon as a task is done and checked (`php -l`, quick browser look when it has UI), tick it in `docs/ROADMAP.md`. If you must stop mid-task, mark it `[~]` and write a one-line note under **Notes** saying exactly what is left.
- Keep `CLAUDE.md` → Module contracts current while you work (markup contracts, theme quirks, cache rules). Write contracts, not a changelog.
- Every new user-facing string: `__()` with the `lenz-plus` text domain. Every file/class/public method: a docblock. Comments explain why.
- Port from Studiare instead of rewriting whenever the roadmap says "port"; keep its structure so the two plugins stay comparable.

## 3. Definition of done (end of the phase)

Run these and fix everything they report:

```bash
find lenz-plus -name '*.php' -print0 | xargs -0 -n1 php -l | grep -v 'No syntax errors' || true
find lenz-plus/assets -name '*.js' ! -name '*.min.js' -print0 | xargs -0 -n1 node --check
vendor/bin/phpcs                      # must be 0 errors, 0 warnings (vendor/bin/phpcbf fixes formatting)
node tools/minify.mjs                 # after any front-end CSS/JS change
```

Then:
1. **Browser check** with Claude in Chrome through the viewport harness (the window is maximised, `resize_window` has no effect): `http://127.0.0.1:8888/viewport.html?a=/<path>/&w=390` and `&w=1440` (exact-width iframe, same origin, inspect with `frames[0].document`). Check the phase's features, RTL, no console errors (`read_console_messages`), nothing new in `.dev/wp/wp-content/debug.log` (`grep -n 'lenz-plus\|Fatal'`). For design widgets add `&b=/design/<Page>` to see the mockup beside the page and compare spacing, type, colours and states.
2. **Translations:** `bash tools/i18n.sh` (pot + po update, reuses Studiare's translations, lists what is left) → translate the listed entries with `python3 tools/po-fill.py` (JSON on stdin; Persian punctuation «», ZWNJ) → `bash tools/i18n.sh mo`.
3. **Docs:** tick the remaining tasks, set **Current phase** to the next phase, clear Notes that no longer apply, update `CLAUDE.md` (layout tree and contracts).
4. **Commit** (the user asked for one commit per phase):
   ```bash
   git add -A && git commit -m "Phase N: <short summary>" -m "<what landed, 2–5 lines>"
   ```
   End the message with the Co-Authored-By line from the session's attribution instructions. The post-commit hook rebuilds the graph; if it is missing, run `graphify update .`.
   Then push: `git push` (remote `origin` = github.com/hoomanjamshidi/Lenz-Plus, public; the user chose to publish it as is).
5. **Report to the user in Persian:** what landed, what was verified and how, anything skipped or deferred (and why), and what the next phase starts with. Then stop and wait: the user moves to the next phase.

## Rules of thumb

- Never tick a task that has not been verified. Never claim a check ran if it did not.
- A phase that grows too large: split the remaining work into a follow-up task in the roadmap rather than rushing.
- Do not start the next phase in the same turn unless the user asked for it.
