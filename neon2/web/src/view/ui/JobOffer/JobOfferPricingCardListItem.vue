<template>
  <li class="flex gap-2">
    <span :class="[
        'flex-shrink-0 rounded-md size-5',
        'inline-flex items-center justify-center',
      ]" :style="{color:props.colorSet.strong, background:props.colorSet.medium}">
      <Icon name="jobOfferPricingPlanListBulletSpecialCase" v-if="props.specialCase"/>
      <Icon name="jobOfferPricingPlanListBullet" v-else/>
    </span>
    <slot/>
  </li>
</template>

<script setup lang="ts">
import Icon from '../../../neon3/Apps/VueApp/Icon/Icon.vue';
import {ColorSet} from './JobOfferPricingCard.vue';

const props = defineProps<Props>();

interface Props {
  colorSet: ColorSet;
  specialCase?: boolean;
}

export type PlanContent = 'restricted'|'full'|'premium-summary';

const details: Record<PlanContent, string[]> = {
  restricted: [
    'Za pierwsze ogłoszenie każdego miesiąca',
    'Tylko dla organizacji pożytku publicznego, uczelni wyższych oraz firm zatrudniających do 5 osób.',
  ],
  full: [
    'Do wykorzystania w ciągu <b>12 miesięcy</b>',
    'Gwarancja przedstawienia ogłoszenia min. <b>1000 programistom</b> lub zwrot pieniędzy',
    'Gwarancja min. <b>10,000 wyświetleń</b> wizytówki Twojego ogłoszenia na portalu 4programmers',
    'Automatyczne <b>3 podbicia</b>',
    'Do <b>10 lokalizacji</b> na ogłoszenie',
  ],
  'premium-summary': [
    'Pakiet <b>zawiera</b> wszystkie punkty z planu Premium',
  ],
};
</script>
