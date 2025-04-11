import tailwind from "@tailwindcss/vite";
import vue from '@vitejs/plugin-vue';
import {defineConfig} from 'vite';

export default defineConfig({
  base: '/neon2/static/',
  plugins: [
    vue(),
    tailwind(),
  ],
  build: {
    rollupOptions: {
      input: './src/main.ts',
    },
    manifest: 'manifest.json',
  },
});
