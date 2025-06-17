<template>
  <FontAwesomeIcon
    :class="fontAwesomeClasses"
    :fixed-width="props.monospace"/>
</template>

<script setup lang="ts">
import {computed} from "vue";
import {IconName, icons} from "./icons";
import FontAwesomeIcon from "./internal/FontAwesomeIcon.vue";

const props = defineProps<Props>();

interface Props {
  name: IconName;
  monospace?: boolean;
}

const fontAwesomeClasses = computed<string>((): string => icons[props.name]);
</script>
