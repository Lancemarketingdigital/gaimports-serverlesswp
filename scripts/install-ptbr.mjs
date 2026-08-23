import { mkdir, writeFile, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { execFileSync } from 'node:child_process';

const url = 'https://br.wordpress.org/wordpress-7.1-pt_BR.tar.gz';
const archive = join(tmpdir(), 'wordpress-7.1-pt_BR.tar.gz');
const dest = new URL('../wp/wp-content/languages/', import.meta.url);
await mkdir(dest, { recursive: true });
const res = await fetch(url);
if (!res.ok) throw new Error(`Falha ao baixar pt-BR: HTTP ${res.status}`);
await writeFile(archive, Buffer.from(await res.arrayBuffer()));
execFileSync('tar', ['-xzf', archive, '--strip-components=3', '-C', dest.pathname, 'wordpress/wp-content/languages'], { stdio: 'inherit' });
await rm(archive, { force: true });
console.log('WordPress pt-BR instalado no build.');
