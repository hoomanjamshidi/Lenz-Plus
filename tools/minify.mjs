#!/usr/bin/env node
/**
 * Writes minified copies (`*.min.css`, `*.min.js`) of the front-end assets
 * next to their sources. The plugin serves them unless SCRIPT_DEBUG is on
 * (Core\Asset::url()), so PageSpeed and GTmetrix see no "Minify CSS/JS"
 * items even on hosts without a cache plugin. The sources stay readable and
 * are the files to edit; build-zip.sh runs this before packaging.
 *
 * Every CSS/JS source under assets/modules/ is minified, except admin-only
 * files (`*-admin.*`): PageSpeed never measures them and editing is easier
 * when the admin loads the readable source. New front-end files need no
 * registration here.
 *
 * Uses esbuild through npx (downloaded on first run, never shipped).
 * Target `esnext`, so modern syntax (:is(), logical properties, color-mix)
 * is only compressed, never rewritten.
 *
 * Usage: node tools/minify.mjs
 */

import { execFileSync } from 'node:child_process';
import { existsSync, readdirSync } from 'node:fs';
import { dirname, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', 'lenz-plus', 'assets');
const MODULES = join(ROOT, 'modules');

/** Lists front-end sources: .css/.js that are neither minified copies nor admin-only. */
function sources(dir) {
  if (!existsSync(dir)) return [];
  return readdirSync(dir, { withFileTypes: true }).flatMap((entry) => {
    const path = join(dir, entry.name);
    if (entry.isDirectory()) return sources(path);
    const isSource = /\.(css|js)$/.test(entry.name) && !/\.min\.(css|js)$/.test(entry.name);
    return isSource && !/-admin\.(css|js)$/.test(entry.name) ? [path] : [];
  });
}

const files = sources(MODULES).sort();
if (!files.length) {
  console.log('no front-end assets yet');
}

for (const source of files) {
  const target = source.replace(/\.(css|js)$/, '.min.$1');
  execFileSync('npx', ['--yes', 'esbuild@0.25', source, '--minify', '--target=esnext', '--legal-comments=none', `--outfile=${target}`, '--log-level=warning'], { stdio: 'inherit' });
  console.log(relative(ROOT, target));
}
