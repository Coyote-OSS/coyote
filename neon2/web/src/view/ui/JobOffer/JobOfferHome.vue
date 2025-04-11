<template>
  <Design.Button primary-outline @click="emit('add')">
    Dodaj ofertę
  </Design.Button>
  <Design.Tile>
    <Design.TextField
      v-model="searchPhrase"
      placeholder="Szukaj po tytule"
      @change="search">
      <Design.Button test-id="search" icon="jobOfferSearch" primary square/>
    </Design.TextField>
  </Design.Tile>
  <Design.Tile
    v-for="job in props.jobOffers"
    :key="job.id"
    data-testid="jobOfferTitle"
    @click="emit('show', job.id)"
    v-text="job.title"/>
</template>

<script setup lang="ts">
import {ref} from 'vue';
import {JobOffer} from '../../../jobBoard';
import {Design} from "../design/design";

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
