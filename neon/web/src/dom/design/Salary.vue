<template>
  <div class="bg-accent-back text-accent-front p-2 rounded">
    {{ props.salary.from }} - {{ props.salary.to }}
  </div>
</template>

<script setup lang="ts">
import {VueSalary} from "../../Main.vue";

interface Props {
  salary: VueSalary;
}

const props = defineProps<Props>();
</script>
