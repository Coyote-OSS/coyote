<template>
  <div class="py-2 px-3 dropdown rounded-lg flex justify-between">
    <span>
      <Icon :name="props.icon" monospace class="mr-1"/>
      {{ props.title }}
    </span>
    <Icon name="dropdownClosed" class="ml-1"/>
  </div>
</template>

<script setup lang="ts">
import Icon, {IconName} from "./Icon.vue";

const props = defineProps<Props>();

interface Props {
  icon: IconName;
  title: string;
}
</script>
