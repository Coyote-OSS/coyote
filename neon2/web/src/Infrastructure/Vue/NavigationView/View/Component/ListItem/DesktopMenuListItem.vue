<template>
  <template v-if="props.type === 'link' || props.type === 'buttonSecondary'">
    <NavigationLink
      class="flex justify-between items-center hover:text-green2-600 cursor-pointer py-2"
      :action="props.action"
      @action="action">
      {{props.title}}
      <MessageCount :messages-count="props.messagesCount" v-if="props.messagesCount"/>
      <Icon :name="props.icon" monospace v-else-if="props.icon"/>
    </NavigationLink>
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
    <NavigationLink class="flex justify-baseline cursor-pointer py-1" :action="props.action" @action="action">
      <div class="text-neutral2-700 font-semibold min-w-44" v-text="props.title"/>
      <Icon v-if="props.icon" :name="props.icon" monospace/>
    </NavigationLink>
  </template>
</template>

<script setup lang="ts">
import {Design} from "../../../../DesignSystem/design";
import Icon from "../../../../Icon/Icon.vue";
import {IconName} from "../../../../Icon/icons";
import {NavigationAction} from "../../../Port/NavigationService";
import MessageCount from "../MessageCount.vue";
import NavigationLink from "../NavigationLink.vue";

const props = defineProps<Props>();
const emit = defineEmits(['action']);

interface Props {
  type: 'link'|'buttonPrimary'|'buttonSecondary'|'username'|'separatorDesktop'|'separatorMobile'|'spaceMobile';
  icon?: IconName;
  title?: string;
  messagesCount?: number;
  action?: NavigationAction;
}

function click(): void {
  emit('action', props.action);
}

function action(): void {
  emit('action', props.action);
}
</script>
