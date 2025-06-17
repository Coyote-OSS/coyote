<template>
  <div class="relative">
    <ValuePropositionModal
      v-if="vpVisibleFor"
      :company-name="vpVisibleFor.companyName"
      @accept="vpAccept"/>
    <Design.Layout class="bg-body">
      <Design.BannerHeading
        :pricing="props.screen === 'pricing'"
        :back="showHomeLink"
        @back="navigateHome"/>
      <Design.Toast
        v-if="toastTitle"
        :title="toastTitle"/>
      <Design.Toast
        v-if="planBundleToast"
        :title="planBundleToast"
        test-id="planBundle"/>
      <Design.Toast
        test-id="paymentNotification"
        :test-value="props.paymentNotification!"
        v-if="paymentNotificationTitle"
        :title="paymentNotificationTitle"/>
      <Design.Toast
        test-id="paymentStatus"
        v-if="paymentStatusTitle"
        :title="paymentStatusTitle"/>
      <RouterView/>
    </Design.Layout>
  </div>
</template>

<script setup lang="ts">
import {computed, provide} from 'vue';
import {ValuePropositionEvent} from "../../main";
import {PaymentNotification} from "../../neon3/Packages/Core/Application/PaymentProvider";
import {PaymentStatus} from "../../neon3/Packages/Feature/JobBoard/Domain/Model";
import {Toast} from '../view';
import {Design} from "../../neon3/Apps/VueApp/DesignSystem/design";
import {JobBoardProperties} from "./JobBoardProperties";
import ValuePropositionModal from "../../neon3/Packages/Feature/Vp/ValuePropositionModal.vue";

const props = defineProps<JobBoardProperties>();

provide('locationInput', props.locationInput!);
provide('screen', props);

function navigateHome(): void {
  props.uiController.navigate('home', null);
}

function vpAccept(event: ValuePropositionEvent, email?: string): void {
  props.uiController.valuePropositionAccepted(event, email);
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
