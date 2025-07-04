<template>
  <template v-if="props.type === 'link'">
    <div class="group/navItem" v-if="props.forumMenu">
      <div :class="navigationLinkStyle" class="group-hover/navItem:text-green2-500" v-text="props.title" @click="click"/>
      <ForumMenu/>
    </div>
    <div v-else :class="navigationLinkStyle" class="hover:text-green2-500" @click="click" v-text="props.title"/>
  </template>
  <template v-else-if="props.type === 'buttonSecondary'">
    <Button class="text-base h-10" primary-outline :title="props.title" @click="click"/>
  </template>
  <template v-else-if="props.type === 'space'">
    <div class="mr-auto"/>
  </template>
</template>

<script setup lang="ts">
import Button from "../../../../../DesignSystem/Button.vue";
import ForumMenu from "../../ForumMenuDesktop.vue";

const props = defineProps<Props>();
const emit = defineEmits(['click']);

const navigationLinkStyle = 'max-lg:hidden cursor-pointer py-2 px-3 rounded';

interface Props {
  type: 'link'|'buttonSecondary'|'space';
  title?: string;
  forumMenu?: boolean;
}

function click(): void {
  emit('click');
}
</script>
