<template>
  <JobOfferForm
    mode="create"
    :job-offer="newJobOffer"
    @submit="create"/>
</template>

<script setup lang="ts">
import {SubmitJobOffer} from "../../../main";
import JobOfferForm from "./JobOfferForm.vue";

const emit = defineEmits<Emit>();

interface Emit {
  (event: 'create', jobOffer: SubmitJobOffer): void;
}

function create(jobOffer: SubmitJobOffer): void {
  emit('create', jobOffer);
}

const newJobOffer: SubmitJobOffer = {
  title: '',
  description: '',
  companyName: '',
  salaryRangeFrom: 0,
  salaryRangeTo: 0,
  salaryIsNet: true,
  salaryCurrency: 'PLN',
  salaryRate: 'monthly',
  locations: [''],
  companyLogoUrl: '',
  tagNames: [''],
  workMode: 'stationary',
  legalForm: 'employment',
  experience: 'not-provided',
};
</script>
