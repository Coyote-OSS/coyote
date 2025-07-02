<template>
  <span
    class="size-4.5 rounded-xs bg-red-500 text-white text-xs inline-flex items-center justify-center"
    v-text="props.messagesCount"/>
</template>

<script setup lang="ts">
const props = defineProps<Props>();

interface Props {
  messagesCount: number;
}
</script>
