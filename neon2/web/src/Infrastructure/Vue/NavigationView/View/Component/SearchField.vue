<template>
  <div class="max-lg:hidden flex-grow-1">
    <input
      ref="searchInput"
      v-model="searchPhrase"
      class="w-full border border-tile-border rounded-lg p-2 outline-none"
      placeholder="Wyszukaj"
      @update:model-value="search"
      @focus="suspendEntryPoints(true)"
      @keyup.esc="cancelSearch"/>
    <div class="relative" v-if="searchItemsVisible">
      <div class="absolute top-2 w-full bg-tile p-4 rounded-lg border border-tile-border w-110 space-y-4">
        <div v-for="(searchItems, type) in searchItemsGroupedByType">
          <div class="text-neutral2-500 text-sm -mb-1" v-text="searchItemTypeTitle(type)"/>
          <div v-for="searchItem in searchItems" class="whitespace-nowrap truncate mt-2">
            <a class="hover:text-green2-500"
               :href="searchItem.contentHref"
               data-testid="searchItem"
               v-text="searchItem.title"/>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, ref, watch} from "vue";
import {SearchItem, SearchItemType} from "../../../../../Application/Navigation/Port/SearchPrompt";
import {useClickOutside} from "../../../Helper/clickOutside";
import {useNavigationStore} from "../navigationStore";
import {useNavigationService} from "../vue";

const service = useNavigationService();
const store = useNavigationStore();

const searchPhrase = ref<string>('');
const searchInput = ref<HTMLInputElement>();

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

watch(searchHasItems, (newValue: boolean): void => {
  if (newValue) {
    clickOutside.addClickListener(() => searchCloseForced.value = true);
  } else {
    clickOutside.removeAll();
  }
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
