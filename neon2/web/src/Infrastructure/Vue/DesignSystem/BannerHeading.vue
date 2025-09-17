<template>
  <Tile big-space class="tile-highlight-long dark:tile-highlight">
    <p class="mb-1 text-sm text-green-700 dark:text-green-050">
      4programmers
      <span @click="emit('back')" class="cursor-pointer">
        » Praca
      </span>
    </p>
    <div class="text-neutral2-900">
      <div class="text-xl leading-6 font-semibold">
        <h1 class="max-md:hidden">
          Oferty Pracy w IT - Odwiedza nas ponad 150 tys. programistów miesięcznie
        </h1>
        <h1 class="md:hidden mb-1">
          Oferty Pracy w IT
        </h1>
      </div>
      <h2 class="md:hidden">
        Odwiedza nas ponad 150 tys. programistów miesięcznie
      </h2>
    </div>
  </Tile>
</template>

<script setup lang="ts">
import Tile from "./Tile.vue";

const emit = defineEmits(['back']);
</script>
