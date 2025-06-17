<template>
  <Design.Toast title="Ogłoszenie zostało zapisane, zostanie opublikowane kiedy zaksięgujemy płatność."/>
  <JobOfferRedeemBundle
    v-if="store.planBundle?.canRedeem"
    :plan-bundle="store.planBundle"
    :job-offer-id="jobOfferId"/>
  <JobOfferPaymentForm
    v-else
    :job-offer-id="jobOfferId"
    :summary="store.paymentSummary!"
    :countries="store.invoiceCountries!"
    :vat-id-state="store.paymentVatIdState"
    :payment-processing="store.paymentProcessing"/>
</template>

<script setup lang="ts">
import {inject} from "vue";
import {useRoute} from "vue-router";
import {Design} from "../../neon3/Apps/VueApp/DesignSystem/design";
import {JobBoardService} from "../../neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {BoardStore, useBoardStore} from "../../neon3/Apps/VueApp/Modules/JobBoard/store";
import {jobBoardServiceInjectKey} from "../../neon3/Apps/VueApp/Modules/JobBoard/vue";
import JobOfferPaymentForm from "../JobOfferPaymentForm.vue";
import JobOfferRedeemBundle from "../JobOfferRedeemBundle.vue";

const store: BoardStore = useBoardStore();
const service = inject<JobBoardService>(jobBoardServiceInjectKey)!;

const route = useRoute();
const jobOfferId = Number(route.params.id)!;

service.resumePayment(jobOfferId!);
</script>
