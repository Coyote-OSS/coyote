<template>
  <ul class="space-y-3 text-sm/4.5">
    <template v-if="props.content === 'restricted'">
      <JobOfferPricingCardListItem :color-set="props.colorSet">
        Za pierwsze ogłoszenie każdego miesiąca
      </JobOfferPricingCardListItem>
      <JobOfferPricingCardListItem :color-set="props.colorSet" special-case>
        Tylko dla organizacji pożytku publicznego, uczelni wyższych oraz firm zatrudniających do 5 osób.
      </JobOfferPricingCardListItem>
    </template>
    <JobOfferPricingCardListItem v-else :color-set="props.colorSet" v-for="listItem in details[props.content]">
      <span v-html="listItem"/>
    </JobOfferPricingCardListItem>
  </ul>
</template>

<script setup lang="ts">
import {ColorSet} from "./ColorSet";
import JobOfferPricingCardListItem from './JobOfferPricingCardListItem.vue';

const props = defineProps<Props>();

interface Props {
  content: PlanContent;
  colorSet: ColorSet;
}

export type PlanContent = 'restricted'|'full'|'premium-summary';

const details = {
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
