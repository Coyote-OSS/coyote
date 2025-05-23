<template>
  <div class="flex flex-wrap gap-4">
    <div
      v-for="(tag, index) in model"
      class="relative px-4 py-3 rounded-lg border border-neutral2-200">
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
import {BackendJobOfferTagPriority} from "../../../backend";
import {Tag} from "../../../main";
import Icon from "../icons/Icon.vue";
import JobOfferTagPriority from "./JobOfferTagPriority.vue";

const model: ModelRef<Tag[]> = defineModel<Tag[]>({required: true});

function experienceTitle(priority: BackendJobOfferTagPriority): string {
  const titles: Record<BackendJobOfferTagPriority, string> = {
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
</script>
