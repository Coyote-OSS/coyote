import vue from '@vitejs/plugin-vue';
import {defineConfig} from 'vite';

export default defineConfig({
  plugins: [vue()],
  build: {
    rollupOptions: {
      input: './src/main.ts',
    },
    manifest: 'manifest.json',
  },
});
