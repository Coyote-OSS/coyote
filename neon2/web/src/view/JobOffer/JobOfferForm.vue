<template>
  <label>
    Tytuł oferty
    <input id="jobOfferTitle" v-model="jobTitle">
  </label>
  <button @click="emit('create', jobTitle)">
    Dodaj
  </button>
</template>

<script setup lang="ts">
import {ref} from 'vue';

const emit = defineEmits<Emit>();
const jobTitle = ref<string>('');

interface Emit {
  (event: 'create', title: string): void;
}
</script>
