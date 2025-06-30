<template>
  <JobOfferBadge color="primary" :nowrap="props.nowrap" v-text="salaryFormat"/>
</template>

<script setup lang="ts">
import {computed} from "vue";
import JobOfferBadge from "./JobOfferBadge.vue";
import {formatSalary, SalaryJobOffer} from "./JobOfferSalary";

const props = defineProps<Props>();

interface Props {
  salary: SalaryJobOffer;
  nowrap?: boolean;
}

const salaryFormat = computed((): string => formatSalary(props.salary));
</script>
