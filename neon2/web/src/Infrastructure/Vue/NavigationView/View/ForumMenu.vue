<template>
  <div class="absolute top-0 left-1/2 -translate-x-1/2 hidden group-hover/navItem:block">
    <div class="bg-neutral2-100 rounded-lg inline-block font-semibold border border-tile-border shadow-md">
      <div
        class="bg-neutral2-050 text-neutral2-600 text-xs py-3 px-4 space-x-4 rounded-t-lg border-b border-tile-border">
        <span v-for="item in forumMenu.headerItems" :title="item.help">
          <Icon :name="item.icon" class="mr-1" :class="{'text-green-500':item.online}"/>
          {{item.title}}
        </span>
      </div>
      <div class="p-4">
        <div class="flex gap-x-6">
          <div v-for="category in forumMenu.categories">
            <span class="text-neutral2-800 whitespace-nowrap">
              <Icon :name="category.icon" class="mr-2"/>
              <a v-if="category.href" :href="category.href" v-text="category.title"/>
              <span v-else v-text="category.title"/>
            </span>
            <hr class="text-tile-border mt-1 mb-1.5">
            <ul>
              <li class="text-neutral2-500 text-xs group/item" v-for="item in category.items">
                <a class="flex justify-between gap-15 py-1.5" :href="item.href">
                  <div>
                    <div class="text-sm whitespace-nowrap">
                      <span class="group-hover/item:text-green2-600 font-medium" v-text="item.title"/>
                      <span class="ml-1 text-xs text-[#ebb305]" title="Promowana kategoria." v-if="item.promoted">
                        <Icon name="navigationCategoryPromoted"/>
                      </span>
                      <span class="ml-1 text-xs text-green-500"
                            title="Kategoria o wzrastającej popularności."
                            v-if="item.trending">
                        <Icon name="navigationCategoryTrending"/>
                      </span>
                    </div>
                    <div
                      class="font-normal whitespace-nowrap text-neutral2-400 group-hover/item:text-neutral2-500"
                      v-if="item.subtitle"
                      v-text="item.subtitle"/>
                  </div>
                  <span class="font-normal whitespace-nowrap relative" v-if="item.count">
                    {{item.count.short}}
                    <span
                      class="absolute right-0 bg-neutral2-100 overflow-hidden transition-[max-width] max-w-0 group-hover/item:max-w-25"
                      v-text="item.count.long"/>
                  </span>
                </a>
              </li>
            </ul>
          </div>
        </div>
        <div class="text-green-500 text-sm mt-6">
          <a :href="forumMenu.allCategoriesHref">
            Zobacz wszystkie kategorie
            <Icon name="navigationAllCategories" class="ml-1"/>
          </a>
        </div>
      </div>
      <div
        v-if="forumMenu.footerItems.length"
        class="bg-neutral2-050 text-neutral2-600 text-xs py-3 px-4 space-x-4 rounded-b-lg border-t border-tile-border">
        <a
          v-for="item in forumMenu.footerItems"
          :href="item.href"
          :title="item.help"
          v-text="item.title"/>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Icon from "../../Icon/Icon.vue";
import {NavigationForumMenu} from "../NavigationForumMenu";
import {useNavigationStore} from "../navigationStore";

const store = useNavigationStore();

const forumMenu = computed<NavigationForumMenu>(() => store.$state.navigationForumMenu!);
</script>
