<template>
  <button @click="emit('add')">Dodaj ofertę</button>
  <div>
    <input placeholder="Wyszukaj" v-model="searchPhrase" @input="search"/>
    <button data-testid="search">S</button>
  </div>
  <ul>
    <li
      v-for="job in props.jobOffers"
      :key="job.id"
      data-testid="jobOfferTitle"
      @click="emit('show', job.id)"
      v-text="job.title"/>
  </ul>
</template>

<script setup lang="ts">
import {ref} from 'vue';
import {JobOffer} from '../../../jobBoard';

const props = defineProps<Props>();

interface Props {
  jobOffers: JobOffer[];
}

const emit = defineEmits<Emit>();

interface Emit {
  (event: 'show', id: number): void;
  (event: 'add'): void;
  (event: 'search', searchPhrase: string): void;
}

const searchPhrase = ref<string>('');

function search(): void {
  emit('search', searchPhrase.value);
}
</script>
