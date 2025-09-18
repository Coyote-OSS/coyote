<template>
  <li class="flex gap-2">
    <span :class="[
        'flex-shrink-0 rounded-md size-5',
        'inline-flex items-center justify-center',
        props.colorSet.mediumCssClass,
        props.colorSet.strongCssClass,
      ]">
      <Icon name="jobOfferPricingPlanListBulletSpecialCase" v-if="props.specialCase"/>
      <Icon name="jobOfferPricingPlanListBullet" v-else/>
    </span>
    <slot/>
  </li>
</template>

<script setup lang="ts">
import Icon from '../../../Icon/Icon.vue';
import {ColorSet} from "./ColorSet";

const props = defineProps<Props>();

interface Props {
  colorSet: ColorSet;
  specialCase?: boolean;
}
</script>
