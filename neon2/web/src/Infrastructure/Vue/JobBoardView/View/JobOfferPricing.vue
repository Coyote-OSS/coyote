<template>
  <p class="text-center text-neutral2-500 text-lg mb-6 mt-12">
    4programmers każdego miesiąca odwiedza ponad <b>150 000+</b> programistów.
  </p>
  <div class="flex justify-center gap-2">
    <JobOfferPricingTab v-model="pricingTab"/>
  </div>
  <div class="flex gap-4" :class="pricingTab === 'offers' ? 'lg:w-2/3 mx-auto' : ''">
    <JobOfferPricingCard
      v-if="pricingTab === 'offers'"
      v-for="plan in offerPlans"
      :plan="plan.name"
      :title="plan.title"
      :bundle-size="plan.bundleSize"
      :price="plan.price + ' PLN'"
      :expiresIn="plan.expiresIn + ' dni'"
      :button-title="buttonTitle(plan)"
      :content="plan.free ? 'restricted' : 'full'"
      :color="plan.color"
      @select="p => service.selectPlan(p)"/>
    <JobOfferPricingCard
      v-else
      v-for="plan in bundlePlans"
      :plan="plan.name"
      :title="plan.title"
      :bundle-size="plan.bundleSize"
      :price="plan.price + ' PLN'"
      :bundlePrice="plan.bundlePrice ? plan.bundlePrice + ' PLN' : undefined"
      :expiresIn="plan.expiresIn + ' dni'"
      :button-title="buttonTitle(plan)"
      :content="plan.bundleDiscount ? 'premium-summary' : 'full'"
      :bundle-discount="plan.bundleDiscount"
      :color="plan.color"
      @select="p => service.selectPlan(p)"/>
  </div>
  <JobOfferPricingTestimonial class="mt-16"/>
</template>

<script setup lang="ts">
import {ref} from 'vue';
import {PricingPlan} from "../../../../Domain/JobBoard/JobBoard";
import {bundlePlans, offerPlans, PlanCard} from "../../../../Domain/JobBoard/pricingPlans";
import JobOfferPricingCard from './component/JobOfferPricingCard.vue';
import JobOfferPricingTab, {PricingTab} from './component/JobOfferPricingTab.vue';
import JobOfferPricingTestimonial from "./component/JobOfferPricingTestimonial.vue";
import {useJobBoardService} from "./vue";

const service = useJobBoardService();
const pricingTab = ref<PricingTab>('offers');

function buttonTitle(plan: PlanCard): string {
  const titles: Record<PricingPlan, string> = {
    'free': 'Publikuj ogłoszenie',
    'premium': 'Kup ogłoszenie',
    'strategic': 'Kup pakiet Strategic',
    'growth': 'Kup pakiet Growth',
    'scale': 'Kup pakiet Scale',
  };
  return titles[plan.name];
}
</script>
