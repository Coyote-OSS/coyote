<template>
  <a :href="tile.jobOfferHref" :class="[
    'border border-tile-outline bg-gray-100 text-gray-800',
    'flex flex-col no-underline tile-shadow transition-shadow hover:shadow-md',
    'w-86 shrink-0 gap-2 rounded-2xl p-2',
  ]">
    <div class="flex items-center gap-2">
      <span v-for="pill in tile.headerPills" :key="pill" :class="[
        'bg-gray-50 text-gray-500',
        'inline-flex items-center gap-1',
        'rounded-full px-2 py-1 text-xs',
      ]">
        <Icon name="location" v-if="pill !== 'Remote'"/>
        {{pill}}
      </span>
      <span class="ml-auto flex items-center gap-2">
        <span v-if="tile.isNew" class="bg-blue-100 text-blue-600 rounded p-2 text-xs font-normal">
          Nowe
        </span>
        <span class="flex h-[34px] w-[34px] items-center justify-center rounded">
          <Icon name="favourite" class="text-gray-400 text-base"/>
        </span>
      </span>
    </div>

    <div class="bg-gray-50 flex flex-col gap-2 rounded-lg p-2">
      <div class="flex items-center gap-2">
        <img v-if="tile.companyLogoUrl" :src="tile.companyLogoUrl" :alt="tile.companyName"
             class="h-9 w-9 shrink-0 rounded-lg object-contain"/>
        <div v-else :class="[
          'bg-gray-25 text-gray-400',
          'flex items-center justify-center',
          'h-9 w-9 shrink-0 rounded-lg',
        ]">
          <Icon name="company"/>
        </div>
        <p class="line-clamp-2 text-sm font-normal text-gray-600" v-text="tile.jobOfferTitle"/>
      </div>
      <hr class="separator"/>
      <div class="flex items-center justify-between gap-2">
        <span class="truncate text-sm text-gray-500" v-text="tile.companyName"/>
        <span :class="[
          tile.salaryDisclosed ? 'bg-green-100 text-salary' : 'bg-gray-25 text-gray-500',
          'shrink-0 rounded-lg px-1.5 py-1 text-xs font-medium',
        ]">
          {{tile.salaryFormat}}
        </span>
      </div>
      <template v-if="tile.technologyTags.length > 0">
        <hr class="separator"/>
        <div class="flex flex-wrap gap-1">
          <span v-for="tag in visibleTags" :key="tag.name" :class="[
          'bg-tag text-gray-500',
          'inline-flex items-center gap-1',
          'rounded px-1.5 py-0.5 text-xs',
        ]">
            <img v-if="tag.logoUrl" :src="tag.logoUrl" :alt="tag.name" class="h-3.5 w-3.5"/>
            {{tag.name}}
          </span>
          <span v-if="hiddenTagCount > 0" class="bg-tag text-gray-500 rounded px-1.5 py-0.5 text-xs">
            +{{hiddenTagCount}}
          </span>
        </div>
      </template>
    </div>
  </a>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import type {ForumJobOfferTile} from '../ViewModel/ForumJobOfferTile';
import Icon from '../../../libs/Icon/Icon.vue';

interface Props {
  tile: ForumJobOfferTile;
}

const props = defineProps<Props>();

const MAX_VISIBLE_TAGS = 4;

const visibleTags = computed(() => props.tile.technologyTags.slice(0, MAX_VISIBLE_TAGS));
const hiddenTagCount = computed(() => Math.max(0, props.tile.technologyTags.length - MAX_VISIBLE_TAGS));
</script>
