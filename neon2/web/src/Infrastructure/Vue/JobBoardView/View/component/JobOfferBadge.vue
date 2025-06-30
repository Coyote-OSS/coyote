<template>
  <span class="p-1.5 rounded-lg" :class="[
    colorClass, 
    {'text-sm':props.textSmall}, 
    {'whitespace-nowrap': props.nowrap}]">
    <slot/>
  </span>
</template>

<script setup lang="ts">
import {computed} from "vue";

const props = defineProps<Props>();

interface Props {
  color: BadgeColor;
  textSmall?: boolean;
  nowrap?: boolean;
}

const classes = {
  primary: 'accent',
  gray: 'bg-neutral2-025 text-neutral2-500',
  pink: 'bg-[#e2d2f9] text-[#16062d] dark:bg-[#16062d] dark:text-[#b68cf6]',
};

type BadgeColor = keyof typeof classes;

const colorClass = computed((): string => classes[props.color]);
</script>
