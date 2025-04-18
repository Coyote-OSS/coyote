<template>
  <div class="p-1.5 rounded-lg" :class="[colorClass, textClass]">
    <slot/>
  </div>
</template>

<script setup lang="ts">
import {computed} from "vue";

const props = defineProps<Props>();

interface Props {
  color: BadgeColor;
  textSmall?: boolean;
}

type BadgeColor = 'primary'|'pink'|'gray';

const colorClass = computed((): string => {
  const classes: Record<BadgeColor, string> = {
    'primary': 'bg-accent-back text-accent-front',
    'gray': 'bg-gray-accent-back text-gray-accent-front',
    'pink': 'bg-pink-accent-back text-pink-accent-front',
  };
  return classes[props.color];
});

const textClass = computed((): string => {
  return props.textSmall ? 'text-sm' : '';
});
</script>
