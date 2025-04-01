<template>
  4programmers > JobBoard
  <span v-if="showHomeLink" @click="navigate('home')" style="cursor:pointer;">
    > Wróć
  </span>
  <hr/>
  <JobOfferPricing
    v-if="screen === 'pricing'"
    @select="selectPlan"/>
  <JobOfferForm
    v-if="screen === 'form'"
    :plan="selectedPlan!"
    @create="createJob"/>
  <JobOfferShow
    v-if="screen === 'edit'"
    :job-offer="currentlyEditedJob"
    @update="updateJob"/>
  <JobOfferHome
    v-if="screen === 'home'"
    :job-offers="props.jobOffers"
    @edit="editJob"
    @add="showPricing"/>
</template>

<script setup lang="ts">
import {computed, ref} from 'vue';
import {JobOffer} from '../jobBoard';
import JobOfferForm from './JobOffer/JobOfferForm.vue';
import JobOfferHome from './JobOffer/JobOfferHome.vue';
import JobOfferPricing from './JobOffer/JobOfferPricing.vue';
import JobOfferShow from './JobOffer/JobOfferShow.vue';

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  jobOffers: JobOffer[];
}

interface Emit {
  (event: 'create', title: string, plan: 'free'|'paid'): void;
  (event: 'update', id: number, title: string): void;
}

const screen = ref<string>('home');
const selectedPlan = ref<'free'|'paid'|null>(null);

let currentlyEditedJobId = null;
const currentlyEditedJob = computed<JobOffer>(() => {
  return props.jobOffers.find(offer => offer.id === currentlyEditedJobId)!;
});

function createJob(jobOfferTitle: string, plan: 'free'|'paid'): void {
  emit('create', jobOfferTitle, plan);
  screen.value = 'home';
}

function editJob(id: number): void {
  currentlyEditedJobId = id;
  screen.value = 'edit';
}

function updateJob(id: number, title: string): void {
  emit('update', id, title);
  screen.value = 'home';
}

function selectPlan(plan: 'free'|'paid'): void {
  selectedPlan.value = plan;
  screen.value = 'form';
}

function showPricing(): void {
  screen.value = 'pricing';
}

const showHomeLink = computed<boolean>(() => {
  return screen.value !== 'home';
});
</script>
