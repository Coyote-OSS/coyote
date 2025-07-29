<template>
  <a @click="hrefClick" :href="href" v-if="href">
    <slot/>
  </a>
  <span v-else @click="invokeAction">
    <slot/>
  </span>
</template>

<script setup lang="ts">

import {computed} from "vue";
import {NavigationAction} from "../../Port/NavigationService.js";
import {useNavigationService} from "../vue";

const props = defineProps<Props>();
const emit = defineEmits(['action']);

interface Emit {
  (event: 'action', action: NavigationAction): void;
}

interface Props {
  action: NavigationAction;
}

const service = useNavigationService();

const href = computed<string|null>(() => service.actionHref(props.action));

function hrefClick(event: MouseEvent): void {
  if (!openInNewTab(event)) {
    invokeAction();
    event.preventDefault();
  }
}

function invokeAction(): void {
  emit('action', props.action);
}

function openInNewTab(event: MouseEvent): boolean {
  return event.ctrlKey || event.metaKey;
}
</script>
