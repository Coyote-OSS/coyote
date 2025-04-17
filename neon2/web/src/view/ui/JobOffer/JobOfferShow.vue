<template>
  <Design.Tile>
    <h1>
      Ogłoszenie: {{props.jobOffer.title}}
    </h1>
    <div>
      Ogłoszenie wygasa za:
      <span data-testid="jobOfferExpiresInDays" v-text="props.jobOffer.expiresInDays"/>
      dni
    </div>
    <div>
      <span data-testid="jobOfferDescription" v-text="props.jobOffer.description"/>
    </div>
    <div>
      <span data-testid="jobOfferCompanyName" v-text="props.jobOffer.companyName"/>
    </div>
    <Design.Button primary-outline @click="editJob">Edytuj</Design.Button>
  </Design.Tile>
</template>

<script setup lang="ts">
import {JobOffer} from '../../../jobBoard';
import {Design} from "../design/design";

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
