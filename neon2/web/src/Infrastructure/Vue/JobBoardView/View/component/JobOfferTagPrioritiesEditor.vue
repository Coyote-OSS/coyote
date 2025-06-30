<template>
  <div class="flex flex-wrap gap-4">
    <div
      v-for="(tag, index) in model"
      class="relative px-4 py-3 rounded-lg border border-neutral2-200"
      @click.capture="cycleTagPriority(tag)">
      <Icon name="jobOfferTagRemove" @click="remove(index)" :class="[
        'bg-tile box-content cursor-pointer',
        // Leading is used to vertically center the icon
        'text-lg leading-5 text-center', 
        'absolute -top-2.5 -right-2.5 size-5', 
        'rounded-full border border-neutral2-200'
      ]"/>
      <span
        class="font-semibold text-neutral2-500 pr-3"
        v-text="tag.tagName"/>
      <hr class="my-2 text-neutral2-200">
      <p
        class="text-sm font-semibold text-neutral2-500"
        v-text="experienceTitle(tag.priority)"/>
      <JobOfferTagPriority
        :priority="tag.priority"
        selectable
        @select="priority => tag.priority = priority"/>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ModelRef} from "vue";
import {JobOfferTag} from "../../../../../Domain/JobBoard/JobBoard";
import {JobOfferTagPriority as Priority} from "../../../../../Domain/JobBoard/JobOfferTagPriority";
import Icon from "../../../Icon/Icon.vue";
import JobOfferTagPriority from "./JobOfferTagPriority.vue";

const model: ModelRef<JobOfferTag[]> = defineModel<JobOfferTag[]>({required: true});

function experienceTitle(priority: Priority): string {
  const titles: Record<Priority, string> = {
    0: 'Wybierz...',
    1: 'Junior',
    2: 'Regular',
    3: 'Senior',
  };
  return titles[priority];
}

function remove(tagIndex: number): void {
  model.value = arrayWithout(model.value, tagIndex);
}

function arrayWithout<T>(array: T[], index: number): T[] {
  const copy = [...array];
  copy.splice(index, 1);
  return copy;
}

function cycleTagPriority(tag: JobOfferTag): void {
  tag.priority = (tag.priority + 1) % 4;
}
</script>
