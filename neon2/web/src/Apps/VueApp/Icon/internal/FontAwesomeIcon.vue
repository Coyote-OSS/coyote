<template>
  <i :class="{'fa-fw': props.fixedWidth}"/>
</template>

<script setup lang="ts">
const props = defineProps<Props>();

interface Props {
  fixedWidth?: boolean;
}
</script>
