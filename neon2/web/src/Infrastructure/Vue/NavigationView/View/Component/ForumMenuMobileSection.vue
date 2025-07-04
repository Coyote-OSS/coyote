<template>
  <div class="flex justify-between text-neutral2-600 text-xl py-3" @click="toggleSectionOpen">
    {{section.title}}
    <Icon :name="sectionOpen ? 'mobileSectionClose' : 'mobileSectionOpen'"/>
  </div>
  <a v-if="sectionOpen" v-for="item in props.section.items" class="block py-2" :href="item.href">
    <div class="text-neutral2-500 font-medium" v-text="item.title"/>
    <div class="text-neutral2-400 text-sm" v-text="item.subtitle"/>
  </a>
  <hr class="text-tile-border my-2">
</template>

<script setup lang="ts">
import {ref} from "vue";
import Icon from "../../../Icon/Icon.vue";
import {Section} from "../../NavigationForumMenu";

const props = defineProps<Props>();

interface Props {
  section: Section;
}

const sectionOpen = ref<boolean>(false);

function toggleSectionOpen(): void {
  sectionOpen.value = !sectionOpen.value;
}
</script>
