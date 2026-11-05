<template>
  <div class="bg-neutral2-100 rounded-b-lg font-semibold border border-tile-border shadow-md cursor-default text-base">
    <div class="bg-neutral2-050 text-neutral2-600 text-xs py-3 px-4 flex flex-wrap gap-x-4 gap-y-1 border-b border-tile-border">
      <span v-for="item in forumMenu.headerItems" :title="item.help" class="whitespace-nowrap">
        <Icon :name="item.icon" class="mr-1" :class="{'text-green-500':item.online}"/>
        {{item.title}}
      </span>
      <a class="ml-auto whitespace-nowrap hover:text-green2-600" :href="forumMenu.allCategoriesHref">
        Wszystkie kategorie
      </a>
    </div>
    <div class="p-4">
      <div class="columns-[12rem] gap-x-6">
        <a
          v-for="category in forumMenu.categories"
          class="flex items-center gap-2 py-1.5 text-sm text-neutral2-500 group/item break-inside-avoid"
          :href="category.href">
          <span class="relative size-4 flex-shrink-0 flex items-center justify-center">
            <img v-if="categoryLogo(category.title)" :src="categoryLogo(category.title)!" alt="" class="size-4 object-contain"/>
            <Icon v-else name="navigationForumCategory" class="text-xs text-neutral2-400"/>
            <span
              v-if="!category.isRead"
              title="Nowe posty w tej kategorii"
              class="absolute -top-1 -right-1 size-2 rounded-full bg-red-500 border border-neutral2-100"/>
          </span>
          <span
            class="truncate group-hover/item:text-green2-600"
            :class="category.isRead ? 'font-medium' : 'font-semibold text-neutral2-700'"
            v-text="category.title"/>
          <span class="ml-auto font-normal text-xs text-neutral2-400 whitespace-nowrap" :title="category.count.long" v-text="category.count.short"/>
        </a>
      </div>
    </div>
    <div
      v-if="forumMenu.userFilters.length"
      class="bg-neutral2-050 text-neutral2-600 text-xs py-3 px-4 flex justify-between gap-x-4 border-y border-tile-border">
      <span class="text-neutral2-400 [text-shadow:0_1px_0_rgb(255_255_255/0.9)] dark:[text-shadow:0_-1px_0_rgb(0_0_0/0.7)]">
        Wątki
      </span>
      <span class="flex gap-x-4">
        <a
          v-for="item in forumMenu.userFilters"
          class="whitespace-nowrap hover:text-green2-600"
          :href="item.href"
          :title="item.help"
          v-text="item.title"/>
      </span>
    </div>
    <div class="p-4" :class="{'border-t border-tile-border': !forumMenu.userFilters.length}">
      <div class="grid grid-cols-[repeat(auto-fill,minmax(14rem,1fr))] gap-x-4 gap-y-2">
        <a
          v-for="topic in forumMenu.topics"
          class="flex justify-between gap-3 py-2 px-3 text-xs rounded-lg bg-neutral2-050 group/item"
          :href="topic.href">
          <Icon name="navigationForumTopic" class="text-neutral2-400 mt-0.5"/>
          <div class="min-w-0 mr-auto">
            <div class="text-sm text-neutral2-500 group-hover/item:text-green2-600 font-medium truncate" v-text="topic.title"/>
            <div class="font-normal text-neutral2-400 group-hover/item:text-neutral2-500 truncate" v-text="topic.subtitle"/>
          </div>
          <span class="font-normal text-neutral2-500 whitespace-nowrap" :title="topic.count.long" v-text="topic.count.short"/>
        </a>
      </div>
      <div class="flex justify-end mt-3">
        <a class="text-green-500 text-sm" :href="forumMenu.allTopicsHref">
          Zobacz wszystkie wątki
          <Icon name="navigationAllTopics" class="ml-1"/>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Icon from "../../../Icon/Icon.vue";
import {NavigationForumMenu} from "../../NavigationForumMenu";
import {useNavigationStore} from "../navigationStore";
import {categoryLogo} from "./categoryLogo";

const store = useNavigationStore();

const forumMenu = computed<NavigationForumMenu>(() => store.$state.navigationForumMenu!);
</script>
