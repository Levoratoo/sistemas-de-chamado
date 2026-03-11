import { defineConfig } from 'vite';
import { resolve } from 'node:path';

const repoName = process.env.GITHUB_REPOSITORY?.split('/')[1];
const pagesBasePath = process.env.PAGES_BASE_PATH;
const base = pagesBasePath || (process.env.GITHUB_ACTIONS && repoName ? `/${repoName}/` : '/');

export default defineConfig({
    root: resolve(__dirname, 'portfolio'),
    publicDir: resolve(__dirname, 'public'),
    base,
    build: {
        outDir: resolve(__dirname, 'out'),
        emptyOutDir: true,
    },
});
