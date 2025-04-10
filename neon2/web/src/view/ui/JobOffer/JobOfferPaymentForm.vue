<template>
  <p>Oferta została stworzona, zostanie opublikowana kiedy zaksięgujemy płatność.</p>
  <button @click="pay">Zapłać</button>
  <div id="creditCardInput"></div>
</template>

<script setup lang="ts">
import {onBeforeUnmount, onMounted} from "vue";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  jobOfferId: number;
}

interface Emit {
  (event: 'pay', jobOfferId: number): void;
  (event: 'mountCardInput', cssSelector: string): void;
  (event: 'unmountCardInput'): void;
}

function pay(): void {
  emit('pay', props.jobOfferId);
}

onMounted(() => emit('mountCardInput', '#creditCardInput'));
onBeforeUnmount(() => emit('unmountCardInput'));
</script>
