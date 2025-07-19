<template>
  <div class="relative hover:text-green2-500 px-2 md:cursor-pointer" @click="action">
    <Icon :name="props.icon" class="size-10 text-xl text-center leading-10"/>
    <Blip v-if="props.number > 0" :value="props.number" important/>
  </div>
</template>

<script setup lang="ts">
import Blip from "../../../DesignSystem/Blip.vue";
import Icon from "../../../Icon/Icon.vue";
import {IconName} from "../../../Icon/icons";

const props = defineProps<Props>();
const emit = defineEmits(['action']);

interface Props {
  number: number;
  icon: IconName;
}

function action(): void {
  emit('action');
}
</script>
