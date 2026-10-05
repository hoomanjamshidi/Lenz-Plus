#!/usr/bin/env node
/**
 * Builds the bundled SVG icon packs from their npm packages (via jsDelivr).
 *
 * Output (committed to the plugin, so the plugin never loads icons from a CDN):
 *   lenz-plus/assets/icons/catalog.json      — semantic keys, labels, pack meta
 *   lenz-plus/assets/icons/packs/<pack>.json — { key: { svg, active? } }
 *
 * Usage: node tools/build-icons.mjs
 */

import { mkdir, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { ICONS, PACKS } from './icon-map.mjs';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const OUT_DIR = join(ROOT, 'lenz-plus', 'assets', 'icons');
const CDN = 'https://cdn.jsdelivr.net/npm';

/**
 * Fetches a file from the CDN; resolves to null on 404 so optional
 * variants (filled icons) can be probed without special casing. Network
 * errors are retried a few times: hundreds of requests run in parallel and
 * a single dropped DNS lookup would otherwise abort the whole build.
 */
async function fetchText(url, attempts = 4) {
  try {
    const res = await fetch(url);
    if (res.status === 404) return null;
    if (!res.ok) throw new Error(`HTTP ${res.status} for ${url}`);
    return res.text();
  } catch (err) {
    if (attempts <= 1) throw err;
    await new Promise((resolve) => setTimeout(resolve, 1500));
    return fetchText(url, attempts - 1);
  }
}

/**
 * Normalises an SVG for inline use: strips comments, XML prologs, the root
 * element's sizing, classes and namespaces, invisible helper paths, and
 * collapses whitespace. Sizing is removed from the root <svg> only: shapes
 * such as <rect> need their own width/height (Lucide's calendar, image, …).
 */
function cleanSvg(svg) {
  return svg
    .replace(/<\?xml[^>]*>/g, '')
    .replace(/<!--[\s\S]*?-->/g, '')
    .replace(/<svg\b[^>]*>/, (tag) => tag.replace(/\s(width|height|class|xmlns(:\w+)?)="[^"]*"/g, ''))
    .replace(/<path stroke="none" d="M0 0h24v24H0z" fill="none"\s*\/>/g, '')
    .replace(/\s+/g, ' ')
    .replace(/\s*(\/?>)/g, '$1')
    .replace(/>\s+</g, '><')
    .trim();
}

async function buildPack(id, pack) {
  const namesKey = pack.namesFrom || id;
  const base = `${CDN}/${pack.npm}@${pack.version}`;
  const icons = {};
  const missing = [];

  await Promise.all(
    ICONS.map(async (icon) => {
      const name = icon.names[namesKey];
      if (!name) return;

      const svg = await fetchText(`${base}/${pack.outline(name)}`);
      if (!svg) {
        missing.push(`${icon.key} (${name})`);
        return;
      }

      const entry = { svg: cleanSvg(svg) };
      if (pack.filled) {
        const filled = await fetchText(`${base}/${pack.filled(name)}`);
        if (filled) entry.active = cleanSvg(filled);
      }
      icons[icon.key] = entry;
    })
  );

  // Keep catalog order stable for readable diffs.
  const ordered = {};
  for (const icon of ICONS) {
    if (icons[icon.key]) ordered[icon.key] = icons[icon.key];
  }

  await writeFile(join(OUT_DIR, 'packs', `${id}.json`), JSON.stringify(ordered));
  const count = Object.keys(ordered).length;
  console.log(`✓ ${id}: ${count}/${ICONS.length} icons` + (missing.length ? ` — missing: ${missing.join(', ')}` : ''));
}

async function main() {
  await mkdir(join(OUT_DIR, 'packs'), { recursive: true });

  for (const [id, pack] of Object.entries(PACKS)) {
    await buildPack(id, pack);
  }

  const catalog = {
    icons: ICONS.map(({ key, label, keywords, fa, lenz = '', lenzActive = '' }) => ({ key, label, keywords, fa, lenz, lenzActive })),
    packs: Object.fromEntries(
      Object.entries(PACKS).map(([id, p]) => [
        id,
        { label: p.label, license: p.license, source: `${p.npm}@${p.version}`, hasFilled: Boolean(p.filled), fallback: p.fallback },
      ])
    ),
  };
  await writeFile(join(OUT_DIR, 'catalog.json'), JSON.stringify(catalog, null, 1));
  console.log('✓ catalog.json');
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
