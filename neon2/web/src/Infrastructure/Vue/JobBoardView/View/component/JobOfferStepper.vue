<template>
  <Design.Stepper
    :step="props.step"
    :steps="steps"/>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import {Design} from "../../../DesignSystem/design";

export type JobOfferCreatorStep = 'company'|'jobOffer'|'preview';
export type JobOfferStep = JobOfferCreatorStep|'publish';

interface Props {
  step: JobOfferStep;
  fourSteps?: boolean;
}

const props = defineProps<Props>();

const steps = computed(() => {
  const steps = [
    {title: 'Informacje o firmie', value: 'company'},
    {title: 'Szczegóły ogłoszenia', value: 'jobOffer'},
    {title: 'Podgląd', value: 'preview'},
  ];
  if (props.fourSteps) {
    steps.push({title: 'Publikacja ogłoszenia', value: 'publish'});
  }
  return steps;
});
</script>
