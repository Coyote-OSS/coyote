<template>
  <h2>
    Wybrany plan: {{ props.plan === 'free' ? 'Darmowy' : 'Płatny' }}
  </h2>
  <label>
    Tytuł oferty
    <input id="jobOfferTitle" v-model="jobTitle">
  </label>
  <button @click="emit('create', jobTitle, props.plan)">
    Dodaj
  </button>
</template>

<script setup lang="ts">
import {ref} from 'vue';

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  plan: 'free'|'paid';
}

interface Emit {
  (event: 'create', title: string, plan: 'free'|'paid'): void;
}

const jobTitle = ref<string>('');
</script>
