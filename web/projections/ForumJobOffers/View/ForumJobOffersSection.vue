<template>
  <section class="tile-backlight flex flex-col items-start gap-6 rounded-lg p-4 sm:p-6">
    <div class="flex w-full items-center justify-between">
      <img :src="logoLight" alt="4programmers.net" class="h-5 dark:hidden"/>
      <img :src="logoDark" alt="4programmers.net" class="hidden h-5 dark:block"/>
      <a :href="jobBoardHref" class="flex items-center gap-1 text-xs font-medium text-green-500 no-underline">
        Zobacz więcej
        <Icon name="chevron-right" class="text-sm"/>
      </a>
    </div>
    <div class="-my-5 flex w-full flex-row gap-3.5 overflow-x-auto py-5">
      <VueForumJobOfferTile
        v-for="(tile, index) in tiles"
        :key="index"
        :tile="tile"
        :class="{'hidden sm:flex': index > 0}"/>
    </div>
  </section>
</template>

<script setup lang="ts">
import Icon from '../../../libs/Icon/Icon.vue';
import logoDark from '../../../../public/img/logo.dark.svg';
import logoLight from '../../../../public/img/logo.light.svg';
import type {ForumJobOfferTile} from '../ViewModel/ForumJobOfferTile';
import VueForumJobOfferTile from './ForumJobOfferTile.vue';

interface Props {
  tiles: ForumJobOfferTile[];
  jobBoardHref: string;
}

defineProps<Props>();
</script>
