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
      @select="screen.uiController.selectPlan"/>
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
      @select="screen.uiController.selectPlan"/>
  </div>
  <JobOfferPricingTestimonial class="mt-16"/>
</template>

<script setup lang="ts">
import {inject, ref} from 'vue';
import {PricingPlan} from "../../../../Packages/Feature/JobBoard/Domain/Model";
import {bundlePlans, offerPlans, PlanCard} from "../../../../Packages/Feature/JobBoard/Domain/plans";
import {UiController} from "../../../../../view/ui/ui";
import JobOfferPricingCard from '../../../../../view/ui/JobOffer/JobOfferPricingCard.vue';
import JobOfferPricingTab, {PricingTab} from '../../../../../view/ui/JobOffer/JobOfferPricingTab.vue';
import JobOfferPricingTestimonial from "../../../../../view/ui/JobOffer/JobOfferPricingTestimonial.vue";

const screen = inject('screen') as Screen;

interface Screen {
  uiController: UiController;
}

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
