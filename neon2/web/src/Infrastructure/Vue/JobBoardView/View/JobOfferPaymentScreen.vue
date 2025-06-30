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
import {Design} from "../../DesignSystem/design";
import {useRouteId} from "../../External/VueRouter";
import {BoardStore, useBoardStore} from "../boardStore";
import JobOfferPaymentForm from "./component/JobOfferPaymentForm.vue";
import JobOfferRedeemBundle from "./component/JobOfferRedeemBundle.vue";
import {useJobBoardService} from "./vue";

const store: BoardStore = useBoardStore();
const service = useJobBoardService();
const jobOfferId = useRouteId();

service.resumePayment(jobOfferId!);
</script>
