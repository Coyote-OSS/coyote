<template>
  <Design.Button primary-outline @click="emit('add')">
    Dodaj ogłoszenie
  </Design.Button>
  <Design.Tile>
    <Design.TextField
      v-model="searchPhrase"
      placeholder="Szukaj po tytule"
      @change="search">
      <Design.Button test-id="search" icon="jobOfferSearch" primary square/>
    </Design.TextField>
  </Design.Tile>
  <Design.JobOfferListItem
    v-for="jobOffer in props.jobOffers"
    :key="jobOffer.id"
    :job-offer="jobOffer"
    @select="emit('show', jobOffer.id)"
    @favourite-change=""/>
  <Design.Material v-if="props.jobOffers.length === 0" nested class="text-word text-center my-2 py-6">
    Nie znaleźliśmy żadnych ofert, pasujących do Twoich kryteriów wyszukiwania.
  </Design.Material>
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
