<template>
  <Design.TextInputOutline>
    <Design.TextInput
      type="plain"
      class="outline-none flex-grow-1"
      :label-id="fieldLabelId"
      :placeholder="props.placeholder"
      v-model="tagPrompt"
      ref="textInput"
      @update:model-value="updateAutocomplete"
      @click="updateAutocomplete(tagPrompt)"
      @keydown.esc="closeAutocomplete"
      @keydown.up.prevent="moveSelection(-1)"
      @keydown.down.prevent="moveSelection(+1)"
      @keydown.enter.prevent="acceptSelected"
      @keydown.space.prevent="acceptSelected"/>
    <slot/>
  </Design.TextInputOutline>
  <div class="relative">
    <Design.Drawer no-space scroll="scrollable" v-if="autocompletedTags.length">
      <p
        v-for="(tag, index) in autocompletedTags"
        class="px-2 py-1.5 rounded hover:bg-green-050 dark:hover:bg-green-950 cursor-pointer flex justify-between"
        :class="{'bg-tile-nested': selected(index)}"
        @click="selectTag(tag)">
        <span class="space-x-1">
          <Design.TagName data-testid="autocompleteValue" v-text="tag.tagName"/>
          <span class="text-sm text-neutral2-400">x{{tag.timesUsed}}</span>
        </span>
        <span class="text-neutral2-400" v-text="tag.title"/>
      </p>
    </Design.Drawer>
  </div>
</template>

<script setup lang="ts">
import {inject, onMounted, ref} from "vue";
import {BackendTag} from "../../../backend";
import {Design} from "../design/design";
import TextInput from "../design/TextInput.vue";
import {TagAutocomplete} from "../ui";
import {useClickOutside} from "../vue/clickOutside";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  placeholder: string;
  autocomplete: TagAutocomplete;
}

const fieldLabelId = inject<string|undefined>('fieldLabelId', undefined);

interface Emit {
  (event: 'select', tagName: string): void;
}

const tagPrompt = ref<string>('');
const autocompletedTags = ref<BackendTag[]>([]);

function updateAutocomplete(text: string): void {
  props.autocomplete(text, (tags: BackendTag[]): void => {
    autocompletedTags.value = tags;
    keyboardCursor.value = 0;
  });
}

const textInput = ref<InstanceType<typeof TextInput>>();
const clickOutside = useClickOutside(false);
const keyboardCursor = ref<number>(0);

onMounted(() => {
  clickOutside.addClickListener(() => closeAutocomplete());
});

function selectTag(tag: BackendTag): void {
  selectTagName(tag.tagName);
}

function acceptSelected(): void {
  selectTagName(autocompletedTags.value[keyboardCursor.value].tagName);
}

function selectTagName(tagName: string): void {
  autocompletedTags.value = [];
  tagPrompt.value = '';
  keyboardCursor.value = 0;
  emit('select', tagName);
  textInput.value!.focus();
}

function closeAutocomplete(): void {
  autocompletedTags.value = [];
}

function selected(index: number): boolean {
  return index === keyboardCursor.value;
}

function moveSelection(step: number): void {
  keyboardCursor.value = clampSelection(keyboardCursor.value + step);
}

function clampSelection(index: number): number {
  return Math.max(Math.min(autocompletedTags.value.length - 1, index), 0);
}
</script>
