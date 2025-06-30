<template>
  <div>
    <p v-text="props.title" class="text-sm text-neutral2-500 mb-1"/>
    <Icon :name="props.icon" v-if="props.icon" class="mr-2"/>
    <span
      class="text-neutral2-700"
      :data-testid="props.testId"
      v-if="props.value">
      <a v-if="props.link"
         v-text="simplifiedUrl(props.value)"
         :href="props.value"
         target="_blank"
         rel="noindex,nofollow"/>
      <span v-else v-text="props.value"/>
      <slot/>
    </span>
    <slot/>
  </div>
</template>

<script setup lang="ts">
import Icon from "../../../Icon/Icon.vue";
import {IconName} from "../../../Icon/icons";
import {simplifiedUrl} from "./JobOfferField";

const props = defineProps<Props>();

interface Props {
  title: string;
  icon?: IconName;
  value?: string;
  testId?: string;
  link?: boolean;
}
</script>
