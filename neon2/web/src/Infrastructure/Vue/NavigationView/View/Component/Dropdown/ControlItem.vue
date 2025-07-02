<template>
  <div class="flex justify-between items-center hover:text-green2-600 cursor-pointer">
    <template v-if="props.title">{{props.title}}</template>
    <slot/>
    <Icon :name="props.icon" v-if="props.icon" monospace/>
  </div>
</template>

<script setup lang="ts">
import Icon from "../../../../Icon/Icon.vue";
import {IconName} from "../../../../Icon/icons";

const props = defineProps<Props>();

interface Props {
  title?: string;
  icon?: IconName;
}
</script>
