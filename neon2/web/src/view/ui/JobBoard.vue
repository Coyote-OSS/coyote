<template>
  <Design.Layout class="bg-body">
    <Design.BannerHeading :back="showHomeLink" @back="navigate('home')"/>
    <Design.Toast v-if="toastTitle" :title="toastTitle"/>
    <Design.Toast v-if="planBundleToast"
                  :title="planBundleToast"
                  test-id="planBundle"/>
    <Design.Toast test-id="paymentNotification"
                  :test-value="props.paymentNotification!"
                  v-if="paymentNotificationTitle"
                  :title="paymentNotificationTitle"/>
    <Design.Toast test-id="paymentStatus"
                  v-if="paymentStatusTitle"
                  :title="paymentStatusTitle"/>
    <JobOfferPricing
      v-if="props.screen === 'pricing'"
      @select="selectPlan"/>
    <JobOfferCreate
      v-if="props.screen === 'form'"
      @create="createJob"/>
    <template v-if="props.screen === 'payment' && props.currentJobOfferId">
      <p>Ogłoszenie zostało zapisane, zostanie opublikowane kiedy zaksięgujemy płatność.</p>
      <JobOfferRedeemBundle
        v-if="props.planBundle?.canRedeem"
        :job-offer-id="props.currentJobOfferId"
        :plan-bundle="props.planBundle"
        @redeem-bundle="redeemBundle"/>
      <JobOfferPaymentForm
        v-else
        :job-offer-id="props.currentJobOfferId"
        @pay="payForJob"
        @mount-card-input="mountCardInput"
        @unmount-card-input="unmountCardInput"/>
    </template>
    <JobOfferShow
      v-if="props.screen === 'show'"
      :job-offer="currentJobOffer"
      @edit="editJob"/>
    <JobOfferEdit
      v-if="props.screen === 'edit'"
      :id="currentJobOffer.id"
      :job-offer="currentJobOffer"
      @update="updateJob"/>
    <JobOfferHome
      v-if="props.screen === 'home'"
      :job-offers="props.jobOffers"
      @show="showJob"
      @add="showJobOfferForm"
      @search="searchJobs"/>
  </Design.Layout>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import {JobOffer} from '../../jobBoard';
import {InvoiceInformation, PlanBundleName, PricingPlan, SubmitJobOffer} from "../../main";
import {PaymentNotification} from "../../paymentProvider";
import {PaymentStatus} from "../../paymentService";
import {Toast} from '../view';
import {Design} from "./design/design";
import JobOfferCreate from "./JobOffer/JobOfferCreate.vue";
import JobOfferEdit from './JobOffer/JobOfferEdit.vue';
import JobOfferHome from './JobOffer/JobOfferHome.vue';
import JobOfferPaymentForm from './JobOffer/JobOfferPaymentForm.vue';
import JobOfferPricing from './JobOffer/JobOfferPricing.vue';
import JobOfferRedeemBundle from "./JobOffer/JobOfferRedeemBundle.vue";
import JobOfferShow from './JobOffer/JobOfferShow.vue';
import {PlanBundle} from "./ui";

const props = defineProps<JobBoardProps>();
const emit = defineEmits<Emit>();

export interface JobBoardProps {
  jobOffers: JobOffer[];
  screen: Screen;
  toast: Toast|null;
  currentJobOfferId: number|null;
  paymentNotification: PaymentNotification|null;
  paymentStatus: PaymentStatus|null;
  planBundle: PlanBundle|null;
  pricingPlan: PricingPlan|null;
}

interface Emit {
  (event: 'show-form'): void;
  (event: 'select-plan', plan: PricingPlan): void;
  (event: 'create', plan: PricingPlan, jobOffer: SubmitJobOffer): void;
  (event: 'update', jobOfferId: number, jobOffer: SubmitJobOffer): void;
  (event: 'navigate', screen: Screen, id: number|null): void;
  (event: 'search', searchPhrase: string);
  (event: 'pay', id: number, invoiceInfo: InvoiceInformation): void;
  (event: 'redeem-bundle', jobOfferId: number): void;
  (event: 'mount-card-input', cssSelector: string): void;
  (event: 'unmount-card-input'): void;
}

export type Screen = 'home'|'edit'|'form'|'payment'|'pricing'|'show';

function navigate(newScreen: Screen, id?: number): void {
  emit('navigate', newScreen, id || null);
}

const currentJobOffer = computed<JobOffer>(() => {
  return props.jobOffers.find(offer => offer.id === props.currentJobOfferId)!;
});

function createJob(jobOffer: SubmitJobOffer): void {
  emit('create', props.pricingPlan!, jobOffer);
}

function updateJob(id: number, jobOffer: SubmitJobOffer): void {
  emit('update', id, jobOffer);
}

function editJob(id: number): void {
  navigate('edit', id);
}

function showJob(id: number): void {
  navigate('show', id);
}

function payForJob(jobOfferId: number, invoiceInfo: InvoiceInformation): void {
  emit('pay', jobOfferId, invoiceInfo);
}

function redeemBundle(jobOfferId: number): void {
  emit('redeem-bundle', jobOfferId);
}

function selectPlan(bundleName: PlanBundleName, pricingPlan: PricingPlan): void {
  emit('select-plan', pricingPlan);
  navigate('form');
}

function showJobOfferForm(): void {
  emit('show-form');
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

const toastTitle = computed<string|null>(() => {
  if (props.toast === null) {
    return null;
  }
  const titles: Record<Toast, string> = {
    created: 'Dodano ogłoszenie!',
    edited: 'Zaktualizowano ogłoszenie!',
    'bundle-used': 'Skorzystałeś z pakietu, żeby opublikować ogłoszenie!',
  };
  return titles[props.toast];
});

const planBundleToast = computed<string|null>(() => {
  if (props.planBundle === null) {
    return null;
  }
  return `Pozostało ${props.planBundle.remainingJobOffers} ogłoszeń z Pakietu ${capitalize(props.planBundle.bundleName)}.`;
});

function capitalize(string: string): string {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

const paymentStatusTitle = computed<string|null>(() => {
  if (props.paymentStatus === null) {
    return null;
  }
  const titles: Record<PaymentStatus, string> = {
    paymentComplete: 'Płatność zaksięgowana!',
    paymentFailed: 'Płatność odrzucona.',
  };
  return titles[props.paymentStatus];
});

const paymentNotificationTitle = computed<string|null>(() => {
  if (props.paymentNotification === null) {
    return null;
  }
  const titles: Record<PaymentNotification, string> = {
    accepted: 'Przyjęto polecenie płatności.',
    declinedCardExpired: 'Karta płatnicza wygasła. Użyj aktualnej karty i spróbuj ponownie.',
    declinedInsufficientFunds: 'Brak wystarczających środków na koncie. Upewnij się, że masz wystarczającą ilość środków lub użyj innej karty.',
    declinedCard: 'Płatność została odrzucona przez bank. Sprawdź dane karty lub skontaktuj się ze swoim bankiem.',
    declinedPayment: 'Nie udało się przetworzyć płatności. Spróbuj ponownie lub wybierz inną metodę płatności.',
    unexpectedProviderResponse: 'Wystąpił nieoczekiwany błąd po stronie operatora płatności. Spróbuj ponownie za chwilę lub wybierz inną metodę płatności.',
  };
  return titles[props.paymentNotification];
});
</script>
