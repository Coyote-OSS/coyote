<template>
  <Design.Toast title="Ogłoszenie zostało zapisane, zostanie opublikowane kiedy zaksięgujemy płatność."/>
  <JobOfferRedeemBundle
    v-if="screen.planBundle?.canRedeem"
    :view-listener="screen.viewListener"
    :job-offer-id="route.routeJobOfferId!"
    :plan-bundle="screen.planBundle"/>
  <JobOfferPaymentForm
    v-else
    :view-listener="screen.viewListener"
    :job-offer-id="route.routeJobOfferId!"
    :summary="screen.paymentSummary"
    :countries="screen.invoiceCountries"
    :vat-id-state="screen.paymentVatIdState"
    :payment-processing="screen.paymentProcessing"/>
</template>

<script setup lang="ts">
import {inject} from "vue";
import {Country, PaymentSummary, VatIdState} from "../../../main";
import {Design} from "../../../neon3/Core/View/DesignSystem/design";
import {RouteProperties} from "../screen/Screens";
import {PlanBundle, ViewListener} from "../ui";
import JobOfferPaymentForm from "./JobOfferPaymentForm.vue";
import JobOfferRedeemBundle from "./JobOfferRedeemBundle.vue";

const screen = inject('screen') as Screen;
const route = defineProps<RouteProperties>();

interface Screen {
  viewListener: ViewListener;
  planBundle: PlanBundle|null;
  invoiceCountries: Country[];
  paymentSummary: PaymentSummary;
  paymentVatIdState: VatIdState;
  paymentProcessing: boolean;
}
</script>
