<template>
  <div class="bg-tile rounded-xl flex-grow-1 basis-1 flex flex-col gap-2" :class="{'p-2': !isPhantom}">
    <div class="flex-grow-1 flex flex-col" :class="[
         isPhantom ? 'rounded-xl' : 'rounded-lg',
         'bg-linear-150', colorSet.backgroundCssClass
       ]">
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
        <template v-if="props.bundlePrice">
          <b :class="['text-4xl font-semibold', colorSet.strongCssClass]" v-text="props.bundlePrice"/>
          <p class="text-sm">
            koszt całego pakietu
          </p>
          <p class="text-sm">
            <b :class="['text-lg font-medium', colorSet.strongCssClass]" v-text="props.price"/>
            za ogłoszenie na <b>{{props.expiresIn}}</b>
          </p>
        </template>
        <template v-else>
          <b :class="['text-4xl font-semibold', colorSet.strongCssClass]" v-text="props.price"/>
          <p class="text-sm">
            za ogłoszenie publikowane <b>{{props.expiresIn}}</b>
          </p>
        </template>
        <hr :class="dividerClass">
        <JobOfferPricingCardList :content="props.content" :color-set="colorSet"/>
      </div>
      <div
        v-if="props.bundleDiscount"
        :class="['p-4 rounded-b-xl text-sm text-center', colorSet.mediumCssClass, colorSet.strongCssClass]">
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
  gray: {
    backgroundCssClass: 'from-[#ebeced] to-[#ebeced40] dark:from-[#3741514d] dark:to-[#00000033]',
    mediumCssClass: 'bg-[#c7c9cc] dark:bg-[#ffffff1a]',
    strongCssClass: 'text-[#2b2e30] dark:text-white',
    strong: '#2b2e30',
  },
  yellow: {
    backgroundCssClass: 'from-[#f3f0d6] to-[#f3f0d640] dark:from-[#fbbf241a] dark:to-[#00000033]',
    mediumCssClass: 'bg-[#dbd7bb] dark:bg-[#fbbf2433]',
    strongCssClass: 'text-[#3d3709] dark:text-white',
    strong: '#3d3709',
  },
  blue: {
    backgroundCssClass: 'from-[#dce7f9] to-[#eff5ff59] dark:from-[#3b82f61a] dark:to-[#00000033]',
    mediumCssClass: 'bg-[#dbeafe] dark:bg-[#3b82f633]',
    strongCssClass: 'text-[#2563eb] dark:text-[#60a5fa]',
    strong: '#2563eb',
  },
  violet: {
    backgroundCssClass: 'from-[#e6e2fd] to-[#ebe8fc59] dark:from-[#7b55f71a] dark:to-[#00000033]',
    mediumCssClass: 'bg-[#cac5e9] dark:bg-[#7b55f733]',
    strongCssClass: 'text-[#3620c2] dark:text-[#9a84fc]',
    strong: '#3620c2',
  },
  green: {
    backgroundCssClass: 'from-[#dcf9db] to-[#e7f7e659] dark:from-[#22c55e1a] dark:to-[#00000033]',
    mediumCssClass: 'bg-[#d5f0d3] dark:bg-[#22c55e33]',
    strongCssClass: 'text-[#028d30] dark:text-[#4ade80]',
    strong: '#028d30',
  },
  phantom: {
    backgroundCssClass: 'from-[#d6dee740] to-[#d2e0e7] dark:from-[#3741514d] dark:to-[#00000033]',
    mediumCssClass: 'bg-transparent',
    strong: 'text-neutral2-950',
  },
};

const dividerClass = computed(() => {
  if (isPhantom.value) {
    return 'text-neutral2-200';
  }
  return 'text-white dark:text-[#6b728033]';
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
