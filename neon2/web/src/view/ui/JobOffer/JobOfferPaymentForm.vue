<template>
  <p>Oferta została stworzona, zostanie opublikowana kiedy zaksięgujemy płatność.</p>
  <p>Zostaniesz przekierowany do formularza płatności online.</p>
  <button @click="pay">Zapłać</button>
</template>

<script setup lang="ts">
const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  jobOfferId: number;
}

interface Emit {
  (event: 'pay', jobOfferId: number): void;
}

function pay(): void {
  emit('pay', props.jobOfferId);
}
</script>
