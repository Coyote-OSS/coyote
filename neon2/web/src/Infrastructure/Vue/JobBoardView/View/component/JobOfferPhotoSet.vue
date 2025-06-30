<template>
  <div class="flex flex-wrap gap-2">
    <Design.ImageUpload
      :upload="props.upload"
      v-for="(photoUrl, index) in photoUrls"
      :model-value="photoUrl"
      @update:model-value="url => updatePhoto(url, index)"/>
    <Design.ImageUpload
      v-for="placeholder in placeholders"
      :upload="props.upload"
      :model-value="null"
      @update:model-value="url => updatePhoto(url, photoUrls.length)"/>
  </div>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import {UploadImage} from "../../../UploadImage";

import {Design} from "../../../DesignSystem/design";

const props = defineProps<Props>();

const photoUrls = defineModel<string[]>({required: true});

const placeholders = computed(() => {
  return [...Array(Math.max(1, 3 - photoUrls.value.length)).keys()];
});

interface Props {
  upload: UploadImage;
}

function updatePhoto(photoUrl: string|null, index: number): void {
  const urls: (string|null)[] = [...photoUrls.value];
  urls.splice(index, 1, photoUrl);
  photoUrls.value = urls.filter(image => image !== null);
}
</script>
