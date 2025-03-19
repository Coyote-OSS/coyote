<template>
  <div
    class="size-8.5 rounded-lg flex items-center justify-center cursor-pointer"
    :class="{'bg-accent-back text-accent-front': props.favourite}"
    @click="toggle">
    <Icon name="jobOfferFavourite"/>
  </div>
</template>

<script setup lang="ts">
import Icon from '../component/Icon.vue';

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  favourite: boolean;
}

interface Emit {
  (event: 'favouriteChange', favourite: boolean): void;
}

function toggle(): void {
  emit('favouriteChange', !props.favourite);
}
</script>
