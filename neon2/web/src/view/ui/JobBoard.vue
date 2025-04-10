<template>
  4programmers > JobBoard > {{screenTitle}}
  <span v-if="showHomeLink" @click="navigate('home')" style="cursor:pointer;">
    > Wróć
  </span>
  <hr/>
  <p v-if="toastTitle" v-text="toastTitle"/>
  <p data-testid="paymentNotification" v-text="paymentNotificationTitle" :data-value="props.paymentNotification"/>
  <p data-testid="paymentStatus" v-text="paymentStatusTitle"/>
  <JobOfferPricing
    v-if="props.screen === 'pricing'"
    @select="selectPlan"/>
  <JobOfferForm
    v-if="props.screen === 'form'"
    :plan="selectedPlan!"
    @create="createJob"/>
  <JobOfferPaymentForm
    v-if="props.screen === 'payment' && props.currentJobOfferId"
    :job-offer-id="props.currentJobOfferId"
    @pay="payForJob"
    @mount-card-input="mountCardInput"
    @unmount-card-input="unmountCardInput"/>
  <JobOfferShow
    v-if="props.screen === 'show'"
    :job-offer="currentJobOffer"
    @edit="editJob"/>
  <JobOfferEdit
    v-if="props.screen === 'edit'"
    :job-offer="currentJobOffer"
    @update="updateJob"/>
  <JobOfferHome
    v-if="props.screen === 'home'"
    :job-offers="props.jobOffers"
    @show="showJob"
    @add="showPricing"
    @search="searchJobs"/>
</template>

<script setup lang="ts">
import {computed, ref} from 'vue';
import {JobOffer} from '../../jobBoard';
import {PaymentNotification} from "../../paymentProvider";
import {PaymentStatus} from "../../paymentService";
import {Toast} from '../view';
import JobOfferEdit from './JobOffer/JobOfferEdit.vue';
import JobOfferForm from './JobOffer/JobOfferForm.vue';
import JobOfferHome from './JobOffer/JobOfferHome.vue';
import JobOfferPaymentForm from './JobOffer/JobOfferPaymentForm.vue';
import JobOfferPricing from './JobOffer/JobOfferPricing.vue';
import JobOfferShow from './JobOffer/JobOfferShow.vue';

export interface JobBoardProps {
  jobOffers: JobOffer[];
  toast: Toast|null;
  screen: Screen;
  currentJobOfferId: number|null;
  paymentNotification: PaymentNotification|null;
  paymentStatus: PaymentStatus|null;
}

export type Screen = 'home'|'edit'|'form'|'payment'|'pricing'|'show';

const props = defineProps<JobBoardProps>();
const emit = defineEmits<Emit>();

interface Emit {
  (event: 'create', title: string, plan: 'free'|'paid'): void;
  (event: 'update', id: number, title: string): void;
  (event: 'navigate', screen: Screen, id: number|null): void;
  (event: 'search', searchPhrase: string);
  (event: 'pay', id: number): void;
  (event: 'mount-card-input', cssSelector: string): void;
  (event: 'unmount-card-input'): void;
}

function navigate(newScreen: Screen, id?: number): void {
  emit('navigate', newScreen, id || null);
}

const selectedPlan = ref<'free'|'paid'|null>(null);

const currentJobOffer = computed<JobOffer>(() => {
  return props.jobOffers.find(offer => offer.id === props.currentJobOfferId)!;
});

function createJob(jobOfferTitle: string, plan: 'free'|'paid'): void {
  emit('create', jobOfferTitle, plan);
}

function editJob(id: number): void {
  navigate('edit', id);
}

function showJob(id: number): void {
  navigate('show', id);
}

function payForJob(jobOfferId: number): void {
  emit('pay', jobOfferId);
}

function updateJob(id: number, title: string): void {
  emit('update', id, title);
}

function selectPlan(plan: 'free'|'paid'): void {
  selectedPlan.value = plan;
  navigate('form');
}

function showPricing(): void {
  navigate('pricing');
}

function searchJobs(searchPhrase: string): void {
  emit('search', searchPhrase);
}

function mountCardInput(cssSelector: string): void {
  emit('mount-card-input', cssSelector);
}

function unmountCardInput(): void {
  emit('unmount-card-input');
}

const showHomeLink = computed<boolean>(() => props.screen !== 'home');

const screenTitle = computed<string>(() => {
  const titles: Record<Screen, string> = {
    home: 'Oferty',
    edit: 'Edycja',
    pricing: 'Pricing',
    form: 'Formularz',
    payment: 'Płatność',
    show: 'Oferta',
  };
  return titles[props.screen];
});

const toastTitle = computed<string|null>(() => {
  if (props.toast === null) {
    return null;
  }
  const titles: Record<Toast, string> = {
    created: 'Dodano ofertę pracy!',
    edited: 'Zaktualizowano ofertę pracy!',
    paid: 'Płatność sfinalizowana!',
  };
  return titles[props.toast];
});

const paymentStatusTitle = computed<string>((): string => {
  const titles: Record<PaymentStatus, string> = {
    paymentComplete: 'Płatność przyjęta!',
    paymentFailed: 'Płatność odrzucona',
  };
  return titles[props.paymentStatus];
});
const paymentNotificationTitle = computed<string>(() => {
  const titles: Record<PaymentNotification, string> = {
    processed: 'Płatność została zaakceptowana.',
    declinedCardExpired: 'Karta płatnicza wygasła. Użyj aktualnej karty i spróbuj ponownie.',
    declinedInsufficientFunds: 'Brak wystarczających środków na koncie. Upewnij się, że masz wystarczającą ilość środków lub użyj innej karty.',
    declinedCard: 'Płatność została odrzucona przez bank. Sprawdź dane karty lub skontaktuj się ze swoim bankiem.',
    declinedPayment: 'Nie udało się przetworzyć płatności. Spróbuj ponownie lub wybierz inną metodę płatności.',
    unexpectedProviderResponse: 'Wystąpił nieoczekiwany błąd po stronie operatora płatności. Spróbuj ponownie za chwilę lub wybierz inną metodę płatności.',
  };
  return titles[props.paymentNotification];
});
</script>
