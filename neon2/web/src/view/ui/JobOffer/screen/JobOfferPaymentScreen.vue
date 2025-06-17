<template>
  <Design.Toast title="Ogłoszenie zostało zapisane, zostanie opublikowane kiedy zaksięgujemy płatność."/>
  <JobOfferRedeemBundle
    v-if="store.planBundle?.canRedeem"
    :plan-bundle="store.planBundle"
    :job-offer-id="route.routeJobOfferId!"/>
  <JobOfferPaymentForm
    v-else
    :job-offer-id="route.routeJobOfferId!"
    :summary="store.paymentSummary!"
    :countries="store.invoiceCountries!"
    :vat-id-state="store.paymentVatIdState"
    :payment-processing="store.paymentProcessing"/>
</template>

<script setup lang="ts">
import {Design} from "../../../../neon3/Apps/VueApp/DesignSystem/design";
import {BoardStore, useBoardStore} from "../../../../neon3/Apps/VueApp/Modules/JobBoard/store";
import {RouteProperties} from "../../screen/Screens";
import JobOfferPaymentForm from "../JobOfferPaymentForm.vue";
import JobOfferRedeemBundle from "../JobOfferRedeemBundle.vue";

const route = defineProps<RouteProperties>();
const store: BoardStore = useBoardStore();
</script>
