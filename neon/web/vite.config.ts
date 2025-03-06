import tailwind from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import {defineConfig} from 'vite';

export default defineConfig({
  base: '/neon/',
  publicDir: './static/',
  plugins: [
    tailwind(),
    vue(),
  ],
  build: {
    rollupOptions: {
      input: './src/main.ts',
    },
    outDir: './public/',
    manifest: 'manifest.json',
  },
});
