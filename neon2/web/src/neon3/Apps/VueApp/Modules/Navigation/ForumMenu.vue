<template>
  <div class="bg-tile rounded-lg inline-block font-semibold border border-tile">
    <div class="bg-neutral2-050 text-neutral2-600 text-xs py-3 px-4 space-x-4 rounded-t-lg">
      <span v-for="item in store.$state.headerItems">
        <Icon :name="item.icon" class="mr-1"/>
        {{item.title}}
      </span>
    </div>
    <div class="p-4">
      <div class="flex gap-x-6">
        <div v-for="category in store.$state.bodyItems.categories">
          <span class="text-neutral2-800">
            <Icon :name="category.icon" class="mr-2"/>
            {{category.title}}
          </span>
          <hr class="text-neutral2-050 mt-1 mb-3">
          <ul class="space-y-3">
            <li v-for="item in category.items" class="text-neutral2-500 text-xs">
              <div class="flex justify-between gap-5">
                <div>
                  <div class="text-sm text-neutral2-600 whitespace-nowrap" v-text="item.title"/>
                  <div class="font-normal whitespace-nowrap" v-text="item.subtitle" v-if="item.subtitle"/>
                </div>
                <span v-if="item.count" v-text="item.count" class="font-normal"/>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <div class="text-green-500 text-sm mt-6">
        Zobacz wszystkie kategorie
        <Icon name="navigationAllCategories" class="ml-1"/>
      </div>
    </div>
    <div class="bg-neutral2-050 text-neutral2-600 text-xs py-3 px-4 space-x-4 rounded-b-lg">
      <span v-for="item in store.$state.footerItems" v-text="item"/>
    </div>
  </div>
</template>

<script setup lang="ts">
import Icon from "../../Icon/Icon.vue";
import {useNavigationStore} from "./store";

const store = useNavigationStore();
</script>
