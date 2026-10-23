import { readFileSync, writeFileSync } from 'node:fs';
const lock = JSON.parse(readFileSync('package-lock.json', 'utf8'));
writeFileSync('resources/build-info.json', JSON.stringify({
    node: process.versions.node,
    npm: process.env.npm_config_user_agent?.match(/npm\/([^ ]+)/)?.[1] ?? null,
    vite: lock.packages['node_modules/vite']?.version ?? null,
    built_at: new Date().toISOString(),
}, null, 2) + '\n');
