<template>
  <div :class="['flex-grow-1', {'max-xl:hidden': !store.navigationEntryPointsSuspended}, $attrs.class]">
    <input
      ref="searchInput"
      v-model="searchPhrase"
      class="w-full border border-tile-border rounded-lg p-2 outline-none"
      placeholder="Wyszukaj"
      @update:model-value="search"
      @focus="suspendEntryPoints(true)"
      @keyup.esc="cancelSearch"
      @click.stop/>
    <div class="relative" v-if="searchItemsVisible" @click.stop>
      <div class="absolute top-2 w-full xl:min-w-100 bg-tile p-4 rounded-lg border border-tile-border w-110 space-y-4">
        <div v-for="(searchItems, type) in searchItemsGroupedByType">
          <div class="text-neutral2-500 text-sm" v-text="searchItemTypeTitle(type)"/>
          <a class="block hover:accent whitespace-nowrap truncate py-1 px-2 rounded -mx-2"
             v-for="searchItem in searchItems"
             :href="searchItem.contentHref"
             data-testid="searchItem"
             v-text="searchItem.title"/>
        </div>
      </div>
    </div>
  </div>
  <Icon
    v-if="!store.navigationEntryPointsSuspended"
    name="navigationSearch"
    class="px-1 mr-1 cursor-pointer xl:hidden"
    :class="[$attrs.class]"
    @click.stop="searchStart"/>
</template>

<script setup lang="ts">
import {computed, nextTick, onMounted, ref} from "vue";
import {SearchItem, SearchItemType} from "../../../../../Application/Navigation/Port/SearchPrompt";
import {useClickOutside} from "../../../Helper/clickOutside";
import Icon from "../../../Icon/Icon.vue";
import {useNavigationStore} from "../navigationStore";
import {useNavigationService} from "../vue";

const service = useNavigationService();
const store = useNavigationStore();

const searchPhrase = ref<string>('');
const searchInput = ref<HTMLInputElement>();

function searchStart(): void {
  suspendEntryPoints(true);
  nextTick(() => {
    searchInput.value!.focus();
  });
}

function search(): void {
  service.search(searchPhrase.value);
  searchCloseForced.value = false;
}

function suspendEntryPoints(suspended: boolean): void {
  store.navigationEntryPointsSuspended = suspended;
}

function cancelSearch(): void {
  suspendEntryPoints(false);
  store.searchItems = [];
  searchPhrase.value = '';
  searchInput.value!.blur();
}

const searchCloseForced = ref<boolean>(false);
const clickOutside = useClickOutside(false);
const searchHasItems = computed<boolean>(() => {
  return store.searchItems.length > 0;
});

onMounted(() => {
  clickOutside.addClickListener(() => {
    cancelSearch();
  });
});

const searchItemsVisible = computed<boolean>(() => {
  return searchHasItems.value && !searchCloseForced.value;
});

function searchItemTypeTitle(type: SearchItemType): string {
  const titles: Record<SearchItemType, string> = {
    jobOffer: 'Oferty pracy',
    user: 'Użytkownicy',
    topic: 'Wątki',
    article: 'Artykuły',
  };
  return titles[type];
}

const searchItemsGroupedByType = computed<GroupedByType>(() => {
  return Object.groupBy(store.searchItems, searchItem => searchItem.type);
});

interface GroupedByType {
  [type: SearchItemType]: SearchItem[];
}
</script>
