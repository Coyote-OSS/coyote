<template>
  <Design.Tile vertical space>
    <p v-text="props.title" class="text-lg font-semibold"/>
    <slot/>
  </Design.Tile>
</template>

<script setup lang="ts">
import {Design} from "./design";

const props = defineProps<Props>();

interface Props {
  title: string;
}
</script>
