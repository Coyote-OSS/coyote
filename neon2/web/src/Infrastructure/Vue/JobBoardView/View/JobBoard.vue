<template>
  <div class="relative">
    <Design.Layout class="bg-body">
      <NavigationTopbar/>
      <template v-if="!navigationStore.$state.navigationMainContentSuspended">
        <div class="pt-3 px-2 max-w-264 mx-auto space-y-3">
          <Design.BannerHeading @back="navigateHome"/>
          <Design.Toast
            v-if="toastTitle"
            :title="toastTitle"/>
          <Design.Toast
            v-if="planBundleToast"
            :title="planBundleToast"
            test-id="planBundle"/>
          <Design.Toast
            test-id="paymentNotification"
            :test-value="store.paymentNotification!"
            v-if="paymentNotificationTitle"
            :title="paymentNotificationTitle"/>
          <Design.Toast
            test-id="paymentStatus"
            v-if="paymentStatusTitle"
            :title="paymentStatusTitle"/>
          <RouterView/>
        </div>
        <Footer/>
      </template>
    </Design.Layout>
  </div>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import {PaymentNotification} from "../../../../Application/JobBoard/Port/PaymentProvider";
import {PaymentUpdatedStatus} from "../../../../Domain/JobBoard/JobBoard";
import {Design} from "../../DesignSystem/design";
import Footer from "../../NavigationView/View/Component/Footer.vue";
import NavigationTopbar from "../../NavigationView/View/Component/NavigationTopbar.vue";
import {useNavigationStore} from "../../NavigationView/View/navigationStore";
import {useBoardStore} from "../boardStore";
import {Toast} from "../Model";
import {useJobBoardService} from "./vue";

const store = useBoardStore();
const service = useJobBoardService();
const navigationStore = useNavigationStore();

function navigateHome(): void {
  service.navigate('home', null);
}

const toastTitle = computed<string|null>(() => {
  if (store.toast === null) {
    return null;
  }
  const titles: Record<Toast, string> = {
    created: 'Dodano ogłoszenie!',
    edited: 'Zaktualizowano ogłoszenie!',
    'bundle-used': 'Skorzystałeś z pakietu, żeby opublikować ogłoszenie!',
  };
  return titles[store.toast];
});

const planBundleToast = computed<string|null>(() => {
  if (store.planBundle === null) {
    return null;
  }
  return `Pozostało ${store.planBundle.remainingJobOffers} ogłoszeń z Pakietu ${capitalize(store.planBundle.bundleName)}.`;
});

function capitalize(string: string): string {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

const paymentStatusTitle = computed<string|null>(() => {
  if (store.paymentStatus === null) {
    return null;
  }
  const titles: Record<PaymentUpdatedStatus, string> = {
    paymentComplete: 'Płatność zaksięgowana!',
    paymentFailed: 'Płatność odrzucona.',
  };
  return titles[store.paymentStatus];
});

const paymentNotificationTitle = computed<string|null>(() => {
  if (store.paymentNotification === null) {
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
  return titles[store.paymentNotification];
});
</script>
