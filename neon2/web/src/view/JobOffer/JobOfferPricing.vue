<template>
  <button @click="emit('select', 'free')">
    Publikuj ogłoszenie
  </button>
  <button @click="emit('select', 'paid')">
    Kup ogłoszenie
  </button>
</template>

<script setup lang="ts">
const emit = defineEmits<Emit>();

interface Emit {
  (event: 'select', plan: 'free'|'paid'): void;
}
</script>
