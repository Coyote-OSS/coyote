<template>
  <div class="pt-2">
    <div v-for="section in props.forumMenu.sections">
      <ForumMenuMobileSection :section="section"/>
    </div>
    <a class="flex justify-between text-neutral2-600 text-xl py-3" :href="forumMenu.allCategoriesHref">
      Zobacz wszystkie kategorie
      <Icon name="mobileSectionNavigate"/>
    </a>
  </div>
</template>

<script setup lang="ts">
import Icon from "../../../Icon/Icon.vue";
import {NavigationForumMenu} from "../../NavigationForumMenu";
import ForumMenuMobileSection from "./ForumMenuMobileSection.vue";

const props = defineProps<Props>();

interface Props {
  forumMenu: NavigationForumMenu;
}
</script>
