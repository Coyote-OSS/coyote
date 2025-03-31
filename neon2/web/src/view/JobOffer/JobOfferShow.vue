<template>
  <label>
    Tytuł oferty
    <input id="jobOfferTitle" v-model="jobTitle">
  </label>
  <button @click="updateJob">
    Zapisz
  </button>
</template>

<script setup lang="ts">
import {ref} from 'vue';
import {JobOffer} from '../../jobBoard';

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  jobOffer: JobOffer;
}

interface Emit {
  (event: 'update', id: number, title: string): void;
}

const jobTitle = ref<string>(props.jobOffer.title);

function updateJob(): void {
  emit('update', props.jobOffer.id, jobTitle.value);
}
</script>
