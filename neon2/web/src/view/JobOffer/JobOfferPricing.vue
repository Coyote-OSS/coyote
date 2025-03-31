<template>
  <button @click="emit('select')">
    Publikuj ogłoszenie
  </button>
</template>

<script setup lang="ts">
const emit = defineEmits<Emit>();

interface Emit {
  (event: 'select'): void;
}
</script>
