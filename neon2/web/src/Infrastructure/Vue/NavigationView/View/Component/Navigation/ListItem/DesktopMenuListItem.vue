<template>
  <template v-if="props.type === 'link' || props.type === 'buttonSecondary'">
    <div class="flex justify-between items-center hover:text-green2-600 cursor-pointer py-2" @click="click">
      {{props.title}}
      <MessageCount :messages-count="props.messagesCount" v-if="props.messagesCount"/>
      <Icon :name="props.icon" monospace v-else-if="props.icon"/>
    </div>
  </template>
  <template v-else-if="props.type === 'buttonPrimary'">
    <Design.Button primary class="px-10 my-2" :title="props.title" @click="click"/>
  </template>
  <template v-else-if="props.type === 'separatorDesktop'">
    <hr class="text-tile-border my-2"/>
  </template>
  <template v-else-if="props.type === 'separatorMobile'"/>
  <template v-else-if="props.type === 'spaceMobile'"/>
  <template v-else-if="props.type === 'username'">
    <div class="flex justify-baseline cursor-pointer py-1" @click="click">
      <div class="text-neutral2-700 font-semibold min-w-44" v-text="props.title"/>
      <Icon v-if="props.icon" :name="props.icon" monospace/>
    </div>
  </template>
</template>

<script setup lang="ts">
import {Design} from "../../../../../DesignSystem/design";
import Icon from "../../../../../Icon/Icon.vue";
import {IconName} from "../../../../../Icon/icons";
import MessageCount from "../../MessageCount.vue";

const props = defineProps<Props>();
const emit = defineEmits(['click']);

interface Props {
  type: 'link'|'buttonPrimary'|'buttonSecondary'|'username'|'separatorDesktop'|'separatorMobile'|'spaceMobile';
  icon?: IconName;
  title?: string;
  messagesCount?: number;
}

function click(): void {
  emit('click');
}
</script>
