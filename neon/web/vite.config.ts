import vue from '@vitejs/plugin-vue';
import {defineConfig} from 'vite';

export default defineConfig({
  publicDir: './static/',
  plugins: [
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
