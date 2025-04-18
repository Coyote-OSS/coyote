<template>
  <Design.JobOfferBadge color="primary">
    {{salaryRange}}
    {{props.salary.salaryCurrency}}
    {{props.salary.salaryIsNet ? 'netto' : ''}}
    {{rateTitle}}
  </Design.JobOfferBadge>
</template>

<script setup lang="ts">
import {computed} from "vue";
import {Currency, Rate} from "../../../../main";
import {Design} from "../design";

interface SalaryJobOffer {
  salaryRangeFrom: number;
  salaryRangeTo: number;
  salaryIsNet: boolean;
  salaryCurrency: Currency;
  salaryRate: Rate;
}

interface Props {
  salary: SalaryJobOffer;
}

const props = defineProps<Props>();

const rateTitle = computed((): string => {
  const titles: Record<Rate, string> = {
    'hourly': '/ h',
    'weekly': '/ tygodniowo',
    'monthly': '',
    'yearly': '/ rocznie',
  };
  return titles[props.salary.salaryRate];
});

const salaryRange = computed((): string => {
  if (props.salary.salaryRangeFrom === props.salary.salaryRangeTo) {
    return formatNumber(props.salary.salaryRangeFrom);
  }
  return `${formatNumber(props.salary.salaryRangeFrom)} - ${formatNumber(props.salary.salaryRangeTo)}`;
});

function formatNumber(number: number): string {
  return addThousandSeparator(number, ' ');
}

function addThousandSeparator(number: number, separator: string) {
  const digits = number.toString();
  if (digits.length > 3) {
    return digits.substring(0, digits.length - 3) + separator + digits.substring(digits.length - 3);
  }
  return digits;
}
</script>
