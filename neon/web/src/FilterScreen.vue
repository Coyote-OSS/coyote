<template>
  <aside :class="[
    'filter-screen',
    'fixed left-0 top-0 w-screen h-screen z-[1031]',
    'flex flex-col'
    ]">
    <div class="flex-grow-1">
      <header class="text-xl font-semibold flex items-center justify-between p-6 border-b-1">
        Filtruj oferty
        <Icon name="jobOfferFilterClose" @click="close"/>
      </header>
      <main class="p-6">
        <div class="space-y-3">
          <VueDropdown icon="jobOfferFilterTechnology" title="Technologie"/>
          <VueDropdown icon="jobOfferFilterLocation" title="Lokalizacje"/>
          <VueDropdown icon="jobOfferFilterWorkMode" title="Pełny etat"/>
          <VueDropdown icon="jobOfferFilterSalary" title="Wynagrodzenie"/>
          <VueDropdown icon="jobOfferFilterCurrency" title="PLN"/>
        </div>
        <hr class="my-6"/>
        <VueDropdown icon="jobOfferFilterSort" title="Sortuj według"/>
      </main>
    </div>
    <footer class="p-6 border-t-1 flex justify-stretch space-x-4">
      <button class="button py-1 grow-1" @click="clear">
        Wyczyść filtry
      </button>
      <button class="button p-1 grow-3" @click="close">
        Pokaż oferty
      </button>
    </footer>
  </aside>
</template>

<script setup lang="ts">
import Icon from "./Icon.vue";
import VueDropdown from "./VueDropdown.vue";

const emit = defineEmits(['close', 'clear']);

function close(): void {
  emit('close');
}

function clear(): void {
  emit('clear');
}
</script>
