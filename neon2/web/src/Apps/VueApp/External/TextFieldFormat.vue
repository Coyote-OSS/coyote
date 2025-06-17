<template>
  <div class="list-style paragraph-style">
    <div ref="editorContainer"/>
  </div>
</template>

<script setup lang="ts">
import {exec, init} from 'pell';
import {onMounted, ref, watch} from 'vue';
import 'pell/dist/pell.min.css';
import {htmlIconFormatBold, htmlIconFormatListOrdered, htmlIconFormatListUnordered} from "../Icon/icons";

const props = defineProps<Props>();

interface Props {
  placeholder: string;
  testId?: string;
}

const model = defineModel<string>();
const editorContainer = ref<HTMLElement|null>(null);

onMounted(() => {
  const editor = init({
    element: editorContainer.value!,
    onChange(html: string): void {
      model.value = html;
    },
    defaultParagraphSeparator: 'p',
    actions: [
      {icon: htmlIconFormatBold, result: () => exec('bold')},
      {icon: htmlIconFormatListUnordered, result: () => exec('insertUnorderedList')},
      {icon: htmlIconFormatListOrdered, result: () => exec('insertOrderedList')},
    ],
  });
  editor.content.innerHTML = model.value!;
  if (props.testId) {
    editor.content.setAttribute('data-testid', props.testId);
  }
});

watch(model, (newContent?: string): void => {
  const pellContent = editorContainer.value!.querySelector('.pell-content')!;
  if (pellContent.innerHTML !== newContent!) {
    pellContent.innerHTML = newContent!; // this resets cursor to start
  }
});
</script>
