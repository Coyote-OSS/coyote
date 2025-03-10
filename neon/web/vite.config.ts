import {defineConfig} from 'vite';

export default defineConfig({
  publicDir: './static/',
  build: {
    rollupOptions: {
      input: './src/main.ts',
    },
    outDir: './public/',
    manifest: 'manifest.json',
  },
});
