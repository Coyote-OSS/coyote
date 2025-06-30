<template>
  <div class="bg-tile rounded-xl flex-grow-1 basis-1 flex flex-col gap-2" :class="{'p-2': !isPhantom}">
    <div class="flex-grow-1 flex flex-col"
         :class="isPhantom ? 'rounded-xl' : 'rounded-lg'"
         :style="{background: colorSet.weak}">
      <div class="flex-grow-1 space-y-3" :class="isPhantom ? 'p-6' : 'p-4'">
        <div class="flex items-center gap-2">
          <JobOfferPricingCardIcon :plan="props.plan" :phantom="isPhantom"/>
          <p v-text="props.title" class="text-xl font-medium"/>
        </div>
        <p class="font-medium">
          <b v-text="props.bundleSize"/>
          {{publishedOffersTitle}}
        </p>
        <hr :class="dividerClass">
        <b class="text-4xl font-semibold" :style="{color: colorSet.strong}" v-text="props.price"/>
        <p class="text-sm">
          za ogłoszenie publikowane <b>{{props.expiresIn}}</b>
        </p>
        <p v-if="props.bundlePrice" class="text-sm">
          <b class="text-lg font-medium" :style="{color: colorSet.strong}" v-text="props.bundlePrice"/>
          koszt całego pakietu
        </p>
        <hr :class="dividerClass">
        <JobOfferPricingCardList :content="props.content" :color-set="colorSet"/>
      </div>
      <div
        v-if="props.bundleDiscount"
        :style="{background: colorSet.medium, color: colorSet.strong}"
        class="p-4 rounded-b-xl text-sm text-center">
        Kup pakiet i zaoszczędź <b v-text="props.bundleDiscount"/>
      </div>
    </div>
    <Design.Button primary @click="select" class="w-full" v-if="!isPhantom">
      {{props.buttonTitle}}
    </Design.Button>
  </div>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import {PricingPlan} from "../../../../../Domain/JobBoard/JobBoard";
import {PricingCardColor} from "../../../../../Domain/JobBoard/pricingPlans";
import {Design} from "../../../DesignSystem/design";
import JobOfferPricingCardIcon from './JobOfferPricingCardIcon.vue';
import JobOfferPricingCardList, {ColorSet} from './JobOfferPricingCardList.vue';

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  plan: PricingPlan;
  title: string;
  bundleSize: number;
  price: string;
  bundlePrice?: string;
  expiresIn: string;
  buttonTitle: string;
  content: PlanContent;
  bundleDiscount?: string;
  phantom?: boolean;
  color: PricingCardColor;
}

interface Emit {
  (event: 'select', pricingPlan: PricingPlan): void;
}

export type PlanContent = 'restricted'|'full'|'premium-summary';

const colorSets: Record<PricingCardColor, ColorSet> = {
  yellow: {weak: '#f7f5e6', medium: '#dbd7bb', strong: '#3d3709'},
  blue: {weak: '#eff5ff', medium: '#dbeafe', strong: '#2563eb'},
  violet: {weak: '#ebe8fc', medium: '#cac5e9', strong: '#3620c2'},
  green: {weak: '#e7f7e6', medium: '#d5f0d3', strong: '#028d30'},
  phantom: {weak: 'var(--color-neutral2-025)', medium: 'transparent', strong: 'var(--color-neutral2-950)'},
};

const dividerClass = computed(() => {
  if (isPhantom.value) {
    return 'text-neutral2-200';
  }
  return 'text-white';
});

const colorSet = computed(() => colorSets[props.color]);

function select(): void {
  emit('select', props.plan);
}

const isPhantom = computed(() => props.color === 'phantom');

const publishedOffersTitle = computed(() => {
  if (props.bundleSize === 1) {
    return 'opublikowane ogłoszenie';
  }
  if (props.bundleSize === 3) {
    return 'opublikowane ogłoszenia';
  }
  return 'opublikowanych ogłoszeń';
});
</script>
