<template>
  <template v-if="props.type === 'link'">
    <div class="group/navItem" v-if="props.forumMenu">
      <div
        :class="navigationLinkStyle"
        class="group-hover/navItem:text-green2-500"
        v-text="props.title"
        @click="click"/>
      <ForumMenu/>
    </div>
    <div v-else :class="navigationLinkStyle" class="whitespace-nowrap group/navItem">
      <div class="hover:text-green2-500" v-text="props.title" @click="click"/>
      <div class="relative hidden group-hover/navItem:block cursor-default" v-if="props.children?.length">
        <div :class="[
          'absolute left-1/2 -translate-x-1/2 top-2',
          'bg-tile p-2 font-medium space-y-2',
          'border rounded-lg border-tile-border',
        ]">
          <div
            v-for="child in props.children"
            class="hover:text-green2-500 p-2 cursor-pointer"
            v-text="child.title"
            @click="action(child.action)"/>
        </div>
      </div>
    </div>
  </template>
  <template v-else-if="props.type === 'buttonSecondary'">
    <Button
      class="text-base h-10 leading-6"
      primary-outline
      :title="props.title"
      @click="click"/>
  </template>
  <template v-else-if="props.type === 'space'">
    <div class="mr-auto"/>
  </template>
</template>

<script setup lang="ts">
import Button from "../../../../DesignSystem/Button.vue";
import {NavigationAction} from "../../../Port/NavigationService";
import {NavigationItem} from "../../Presenter/entryPointItems";
import ForumMenu from "../ForumMenuDesktop.vue";

const props = defineProps<Props>();
const emit = defineEmits(['click']);

const navigationLinkStyle = 'max-lg:hidden cursor-pointer py-2 px-3 rounded';

interface Emit {
  (event: 'action', action: NavigationAction): void;
}

interface Props {
  type: 'link'|'buttonSecondary'|'space';
  title?: string;
  forumMenu?: boolean;
  action?: NavigationAction;
  children?: NavigationItem[];
}

function action(action: NavigationAction): void {
  emit('action', action);
}

function click(): void {
  action(props.action);
}
</script>
