<template>
  <h1>
    Oferta: {{props.jobOffer.title}}
  </h1>
  <div>
    Oferta wygasa za:
    <span data-testid="jobOfferExpiresInDays">
      {{props.jobOffer.expiresInDays}}
    </span>
    dni
  </div>
  <button @click="editJob">Edytuj</button>
</template>

<script setup lang="ts">
import {JobOffer} from '../../../jobBoard';

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  jobOffer: JobOffer;
}

interface Emit {
  (event: 'edit', id: number): void;
}

function editJob(): void {
  emit('edit', props.jobOffer.id);
}
</script>
