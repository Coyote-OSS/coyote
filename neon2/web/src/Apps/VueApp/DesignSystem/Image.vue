<template>
  <div :class="[
      sizeClass, 'rounded-lg overflow-hidden', 
      {'border-4 border-tile box-content': props.border}, 
    ]">
    <img v-if="props.src" :src="props.src" class="bg-tile"/>
    <div v-else :class="[
      sizeClass, 'rounded flex-shrink-0',
      'flex items-center justify-center',
      'accent'
    ]">
      <Icon :name="props.placeholderIcon"/>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Icon from "../Icon/Icon.vue";
import {IconName} from "../Icon/icons";

const props = defineProps<Props>();

interface Props {
  src: string|null;
  placeholderIcon: IconName;
  border?: boolean;
  size?: 'small'|'medium';
}

const sizeClass = computed(() => {
  return props.size === 'small' ? 'size-10' : 'size-14';
});
</script>
