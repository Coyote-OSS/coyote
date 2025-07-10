<template>
  <template v-if="props.type === 'link'">
    <div class="py-3 text-xl active:text-green2-500 flex justify-between" @click="click">
      {{props.title}}
      <div class="w-10 text-center">
        <MessageCount v-if="props.messagesCount" :messages-count="props.messagesCount"/>
        <Icon v-else-if="props.icon" :name="props.icon" monospace/>
      </div>
    </div>
  </template>
  <template v-else-if="props.type === 'username'">
    <div class="flex justify-between items-center py-3" @click="click">
      <div class="text-xl text-neutral2-700 font-semibold" v-text="props.title"/>
      <UserAvatar :user="store.$state.navigationUser"/>
    </div>
  </template>
  <template v-else-if="props.type === 'buttonPrimary'">
    <Design.Button primary class="my-1.5 w-full" :title="props.title" @click="click"/>
  </template>
  <template v-else-if="props.type === 'buttonSecondary'">
    <Design.Button primary-outline class="my-1.5 w-full" :title="props.title" @click="click"/>
  </template>
  <template v-else-if="props.type === 'separatorMobile'">
    <hr class="text-tile-border my-2"/>
  </template>
  <template v-else-if="props.type === 'separatorDesktop'"/>
  <template v-else-if="props.type === 'spaceMobile'">
    <div class="mb-auto"/>
  </template>
</template>

<script setup lang="ts">
import {Design} from "../../../../DesignSystem/design";
import Icon from "../../../../Icon/Icon.vue";
import {IconName} from "../../../../Icon/icons";
import {useNavigationStore} from "../../navigationStore";
import MessageCount from "../MessageCount.vue";
import UserAvatar from "../UserAvatar.vue";

const store = useNavigationStore();
const props = defineProps<Props>();
const emit = defineEmits(['click']);

interface Props {
  type: 'link'|'buttonPrimary'|'buttonSecondary'|'username'|'separatorDesktop'|'separatorMobile'|'spaceMobile';
  title?: string;
  icon?: IconName;
  messagesCount?: number;
}

function click(): void {
  emit('click');
}
</script>
