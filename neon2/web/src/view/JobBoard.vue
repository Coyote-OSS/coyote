<template>
  4programmers > JobBoard
  <span v-if="showHomeLink" @click="navigate('home')" style="cursor:pointer;">
    > Wróć
  </span>
  <hr/>
  <p v-if="toastTitle" v-text="toastTitle"/>
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
import {JobOffer, Toast} from '../jobBoard';
import JobOfferForm from './JobOffer/JobOfferForm.vue';
import JobOfferHome from './JobOffer/JobOfferHome.vue';
import JobOfferPricing from './JobOffer/JobOfferPricing.vue';
import JobOfferShow from './JobOffer/JobOfferShow.vue';

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  jobOffers: JobOffer[];
  toast: Toast|null;
}

interface Emit {
  (event: 'create', title: string, plan: 'free'|'paid'): void;
  (event: 'update', id: number, title: string): void;
  (event: 'navigate'): void;
}

type Screen = 'home'|'edit'|'form'|'pricing';
const screen = ref<Screen>('home');

function navigate(newScreen: Screen): void {
  screen.value = newScreen;
  emit('navigate');
}

const selectedPlan = ref<'free'|'paid'|null>(null);

let currentlyEditedJobId = null;
const currentlyEditedJob = computed<JobOffer>(() => {
  return props.jobOffers.find(offer => offer.id === currentlyEditedJobId)!;
});

function createJob(jobOfferTitle: string, plan: 'free'|'paid'): void {
  emit('create', jobOfferTitle, plan);
  navigate('home');
}

function editJob(id: number): void {
  currentlyEditedJobId = id;
  navigate('edit');
}

function updateJob(id: number, title: string): void {
  emit('update', id, title);
  navigate('home');
}

function selectPlan(plan: 'free'|'paid'): void {
  selectedPlan.value = plan;
  navigate('form');
}

function showPricing(): void {
  navigate('pricing');
}

const showHomeLink = computed<boolean>(() => {
  return screen.value !== 'home';
});

const toastTitle = computed<string|null>(() => {
  if (props.toast === 'created') {
    return 'Dodano ofertę pracy!';
  }
  if (props.toast === 'edited') {
    return 'Zaktualizowano ofertę pracy!';
  }
  return null;
});
</script>
