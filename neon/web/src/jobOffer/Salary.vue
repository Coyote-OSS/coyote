<template>
  <div class="salary">
    {{ props.rangeMin }} - {{ props.rangeMax }}
    {{ currencySign }}
    <template v-if="!props.includesTax">
      netto
    </template>
    {{ settlementTitle }}
  </div>
</template>

<script setup lang="ts">
import {computed} from "vue";
import {Currency, Settlement} from "../main";

const props = defineProps<Props>();

interface Props {
  rangeMin: number|null;
  rangeMax: number|null;
  currency: Currency|null;
  includesTax: boolean;
  settlement: Settlement;
}

const currencySign = computed<string>(() => {
  const currencySigns: Record<Currency, string> = {
    PLN: 'zł',
    EUR: '€',
    USD: '$',
    GBP: '£',
    CHF: '₣',
  };
  return currencySigns[props.currency];
});

const settlementTitle = computed<string>(() => {
  const settlementTitles: Record<Settlement, string> = {
    hourly: '/ godzinowo',
    monthly: '',
    weekly: '/ tygodniowo',
    yearly: '/ rocznie',
  };
  return settlementTitles[props.settlement];
});
</script>
