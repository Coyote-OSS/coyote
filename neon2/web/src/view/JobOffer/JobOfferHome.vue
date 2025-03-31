<template>
  <button @click="emit('add')">Dodaj ofertę</button>
  <div>
    <input placeholder="Wyszukaj"/>
    <button data-testid="search">S</button>
  </div>
  <ul>
    <li
      v-for="job in props.jobOffers"
      :key="job.id"
      data-testid="jobOfferTitle"
      @click="emit('edit', job.id)"
      v-text="job.title"/>
  </ul>
</template>

<script setup lang="ts">
import {JobOffer} from '../../jobBoard';

const props = defineProps<Props>();

interface Props {
  jobOffers: JobOffer[];
}

const emit = defineEmits<Emit>();

interface Emit {
  (event: 'edit', id: number): void;
  (event: 'add'): void;
}
</script>
