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
import {useRouteId} from "../../../../../../Router";
import {Design} from "../../../DesignSystem/design";
import {JobBoardService} from "../JobBoardService";
import {BoardStore, useBoardStore} from "../store";
import {jobBoardServiceInjectKey} from "../vue";
import JobOfferPaymentForm from "./component/JobOfferPaymentForm.vue";
import JobOfferRedeemBundle from "./component/JobOfferRedeemBundle.vue";

const store: BoardStore = useBoardStore();
const service = inject<JobBoardService>(jobBoardServiceInjectKey)!;
const jobOfferId = useRouteId();

service.resumePayment(jobOfferId!);
</script>
