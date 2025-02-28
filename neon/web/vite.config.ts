import tailwind from '@tailwindcss/vite';
import {defineConfig} from 'vite';

export default defineConfig({
  plugins: [
    tailwind(),
  ],
  publicDir: './static',
  build: {
    rollupOptions: {
      input: './main.ts',
    },
    outDir: './public/',
    manifest: 'manifest.json',
  },
});
