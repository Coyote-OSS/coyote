<template>
  <div :class="['flex-grow-1', {'max-xl:hidden': !store.navigationEntryPointsSuspended}, $attrs.class]">
    <input
      ref="searchInput"
      v-model="searchPhrase"
      class="w-full border border-tile-border rounded-lg p-2 outline-none"
      placeholder="Wyszukaj"
      @update:model-value="search"
      @keyup.up="keyboardCursor(-1)"
      @keyup.down="keyboardCursor(+1)"
      @keyup.enter="keyboardSubmit()"
      @keyup.esc="cancelSearch"
      @focus="suspendEntryPoints(true)"
      @click.stop/>
    <div class="relative" v-if="searchItemsVisible" @click.stop>
      <div class="absolute top-2 w-full xl:min-w-100 bg-tile p-4 rounded-lg border border-tile-border w-110">
        <div class="-mt-4"/>
        <template v-for="(listItem, index) in listItems">
          <div
            v-if="listItem.includeType"
            class="text-neutral2-500 text-sm mt-4"
            v-text="searchItemTypeTitle(listItem.typeName)"/>
          <a class="block hover:accent whitespace-nowrap truncate py-1 px-2 rounded -mx-2"
             :class="{'accent': index === keyboardCursorIndex}"
             :href="listItem.searchItem.contentHref"
             data-testid="searchItem"
             v-text="listItem.searchItem.title"/>
        </template>
      </div>
    </div>
  </div>
  <Icon
    v-if="!store.navigationEntryPointsSuspended"
    name="navigationSearch"
    class="size-10 rounded leading-10 text-center mr-1 cursor-pointer xl:hidden hover:accent"
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
const keyboardCursorIndex = ref<number>(-1);

function searchStart(): void {
  suspendEntryPoints(true);
  nextTick(() => {
    searchInput.value!.focus();
  });
}

function search(): void {
  service.search(searchPhrase.value);
  searchCloseForced.value = false;
  keyboardCursorIndex.value = -1;
}

function suspendEntryPoints(suspended: boolean): void {
  store.navigationEntryPointsSuspended = suspended;
}

function cancelSearch(): void {
  suspendEntryPoints(false);
  store.searchItems = [];
  searchPhrase.value = '';
  searchInput.value!.blur();
  keyboardCursorIndex.value = -1;
}

const searchCloseForced = ref<boolean>(false);
const clickOutside = useClickOutside(false);

onMounted(() => {
  clickOutside.addClickListener(() => {
    cancelSearch();
  });
});

const searchItemsVisible = computed<boolean>(() => {
  return store.searchItems.length > 0 && !searchCloseForced.value;
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

const listItems = computed<ListItem[]>(() => {
  const listItems = [];
  const sections = Object.groupBy(store.searchItems, searchItem => searchItem.type);
  let lastType: string|null = null;
  for (const [type, searchItems] of Object.entries(sections)) {
    for (const searchItem of searchItems) {
      listItems.push({searchItem, typeName: type, includeType: type !== lastType});
      lastType = type;
    }
  }
  return listItems;
});

interface ListItem {
  searchItem: SearchItem;
  typeName: string;
  includeType: boolean;
}

function keyboardCursor(update: number): void {
  keyboardCursorIndex.value = (keyboardCursorIndex.value + update) % store.searchItems.length;
  if (keyboardCursorIndex.value < 0) {
    keyboardCursorIndex.value = store.searchItems.length - 1;
  }
}

function keyboardSubmit(): void {
  const listItem = listItems.value[keyboardCursorIndex.value];
  if (listItem) {
    const href = listItem.searchItem.contentHref;
    window.location.href = href;
  } else {
    window.location.href = '/Search?' + new URLSearchParams({q: searchPhrase.value});
  }
}
</script>
