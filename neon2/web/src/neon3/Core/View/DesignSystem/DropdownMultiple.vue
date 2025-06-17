<template>
  <Dropdown
    scroll="scrollablePadding"
    :nested="nested"
    :test-id="props.testId"
    :title="props.title"
    :icon="props.icon"
    :blip="valuesCount"
    :open-to-left="props.openToLeft"
    @keydown.down="cruseSelectedItemIndex(+1)"
    @keydown.up="cruseSelectedItemIndex(-1)"
    @keydown.enter="toggleSelectedItem()"
    @keydown.space.capture.prevent="toggleSelectedItem()">
    <div v-if="search" class="cursor-default mb-2">
      <TextField
        gain-focus
        ref="searchField"
        placeholder="Wyszukaj..."
        @keydown.esc="searchPhrase = ''"
        @keydown.down.prevent
        @keydown.up.prevent
        v-model="searchPhrase"/>
    </div>
    <div class="flex gap-2 flex-wrap max-w-50" v-if="search && model.length">
      <TagName v-for="option in model" @click="toggleAndDeselect(option, false)">
        {{option}}
        <Icon name="dropdownOptionTagRemove"/>
      </TagName>
    </div>
    <CheckBox
      v-for="(option, index) in filteredOptions"
      :ref="el => setOptionItem(option, el as unknown as OptionElement)"
      :key="option.value"
      :label="option.title"
      :icon="option.icon"
      :class="['mb-0 pl-0 p-2 rounded', {'bg-tile-nested': index === selectedItemIndex}]"
      :model-value="selected(option.value)"
      @update:model-value="newValue => toggleAndDeselect(option.value, newValue)"/>
    <span v-if="allFilteredOut" class="text-neutral2-500">
      Brak ofert z "{{searchPhrase.trim()}}".
    </span>
  </Dropdown>
</template>

<script setup lang="ts" generic="T extends string">
import {computed, ref} from "vue";
import Icon from "../Icon/Icon.vue";
import {IconName} from "../Icon/icons";
import CheckBox from "./CheckBox.vue";
import Dropdown from "./Dropdown.vue";
import {clamp} from "./DropdownMultiple";
import {DropdownOption} from "./DropdownOption";
import TagName from "./TagName.vue";
import TextField from "./TextField.vue";

const props = defineProps<Props>();
const model = defineModel<T[]>({type: Array, required: true});

interface Props {
  nested?: boolean;
  testId?: string;
  title: string;
  icon: IconName;
  options: DropdownOption<T>[];
  blip?: string;
  openToLeft?: boolean;
  search?: boolean;
}

function selected(value: T): boolean {
  return model.value.includes(value);
}

function toggle(value: T, checked: boolean): void {
  if (checked) {
    model.value.push(value);
  } else {
    model.value.splice(model.value.indexOf(value), 1);
  }
}

function toggleAndDeselect(value: T, checked: boolean): void {
  toggle(value, checked);
  selectedItemIndex.value = -1;
}

const valuesCount = computed((): string|undefined => {
  if (model.value.length === 0) {
    return undefined;
  }
  return model.value.length.toString();
});

type SearchField = InstanceType<typeof TextField>;
const searchPhrase = ref<string>('');
const searchField = ref<SearchField>();

const allFilteredOut = computed((): boolean => filteredOptions.value.length === 0);
const filteredOptions = computed((): DropdownOption<T>[] => {
  const tags = tagsInSearchPhrase(searchPhrase.value.toLowerCase());
  if (!tags.length) {
    return props.options;
  }
  return tags.flatMap(tag => {
    return props.options
      .filter(option => option.title.toLowerCase().includes(tag))
      .toSorted((a, b): number => searchScore(a.title, tag) - searchScore(b.title, tag));
  });
});

function searchScore(item: string, searchPhrase: string): number {
  return item.indexOf(searchPhrase);
}

function tagsInSearchPhrase(searchPhrase: string): string[] {
  return searchPhrase
    .split(/[,; ]/g)
    .map(tag => tag.trim())
    .filter(tag => tag.length);
}

const selectedItemIndex = ref<number>(-1);

type OptionElement = InstanceType<typeof CheckBox>;
const optionElements = ref<Record<string, OptionElement>>({});

function cruseSelectedItemIndex(step: number): void {
  selectedItemIndex.value = clamp(
    selectedItemIndex.value + step,
    0,
    filteredOptions.value.length - 1);
  elementScrollIntoView(selectedOption.value!);
}

function toggleSelectedItem(): void {
  const value = selectedOption.value!.value;
  toggle(value, !isToggled(value));
}

const selectedOption = computed((): DropdownOption<T>|null => {
  if (selectedItemIndex.value === -1) {
    return null;
  }
  return filteredOptions.value[selectedItemIndex.value];
});

function isToggled(value: T): boolean {
  return model.value.indexOf(value) > -1;
}

function setOptionItem(option: DropdownOption<T>, element: OptionElement): void {
  optionElements.value[option.value] = element;
}

function elementScrollIntoView(option: DropdownOption<T>): void {
  optionElements.value![option.value].scrollIntoView();
}
</script>
