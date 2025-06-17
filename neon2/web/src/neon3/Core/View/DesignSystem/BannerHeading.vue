<template>
  <Tile big-space class="tile-highlight-long dark:tile-highlight">
    <p class="mb-1 text-sm text-green-700 dark:text-green-050">
      4programmers
      <span @click="emit('back')" class="cursor-pointer">
        » Praca
      </span>
      <span v-if="props.back">
        » Ogłoszenie
      </span>
    </p>
    <div class="text-neutral2-900">
      <h1 class="text-xl leading-6 font-semibold" v-if="pricing">
        Znajdź najlepszych programistów szybciej i skuteczniej!
      </h1>
      <template v-else>
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
      </template>
    </div>
  </Tile>
</template>

<script setup lang="ts">
import Tile from "./Tile.vue";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  back?: boolean;
  pricing?: boolean;
}

interface Emit {
  (event: 'back'): void;
}
</script>
