<template>
  <Design.Toast title="Ogłoszenie zostało zapisane, zostanie opublikowane kiedy zaksięgujemy płatność."/>
  <JobOfferRedeemBundle
    v-if="screen.planBundle?.canRedeem"
    :plan-bundle="screen.planBundle"
    :job-offer-id="route.routeJobOfferId!"/>
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
import {VatIdState} from "../../../../main";
import {Design} from "../../../../neon3/Apps/VueApp/DesignSystem/design";
import {JobBoardService} from "../../../../neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {jobBoardServiceInjectKey} from "../../../../neon3/Apps/VueApp/Modules/JobBoard/vue";
import {PlanBundle} from "../../../../neon3/Packages/Feature/JobBoard/Application/Model";
import {Country} from "../../../../neon3/Packages/Feature/JobBoard/Domain/Model";
import {PaymentSummary} from "../../../../neon3/Packages/Feature/JobBoard/Presenter/Model";
import {RouteProperties} from "../../screen/Screens";
import {ViewListener} from "../../ui";
import JobOfferPaymentForm from "../JobOfferPaymentForm.vue";
import JobOfferRedeemBundle from "../JobOfferRedeemBundle.vue";

const screen = inject('screen') as Screen;
const route = defineProps<RouteProperties>();
const service = inject<JobBoardService>(jobBoardServiceInjectKey)!;

interface Screen {
  viewListener: ViewListener;
  planBundle: PlanBundle|null;
  invoiceCountries: Country[];
  paymentSummary: PaymentSummary;
  paymentVatIdState: VatIdState;
  paymentProcessing: boolean;
}
</script>
