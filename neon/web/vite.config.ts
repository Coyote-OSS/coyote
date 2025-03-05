import vue from '@vitejs/plugin-vue';
import {defineConfig} from 'vite';

export default defineConfig({
  publicDir: './static/',
  plugins: [
    vue(),
  ],
  build: {
    lib: {
      entry: './src/main.ts',
      name: 'NeonModule',
      fileName: 'neon',
    },
    rollupOptions: {
      output: {
        assetFileNames: 'assets/[name]-[hash].[ext]',
        chunkFileNames: 'assets/[name]-[hash].js',
        entryFileNames: 'assets/[name]-[hash].js',
      },
    },
    outDir: './public/',
    manifest: 'manifest.json',
  },
  define: {
    'process.env.NODE_ENV': JSON.stringify('production'),
  },
});
