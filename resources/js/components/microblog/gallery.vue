<template>
  <vue-easy-lightbox
    :visible="visible"
    :imgs="lightboxImages"
    :index="index!"
    :move-disabled="true"
    :rotate-disabled="true"
    :zoom-disabled="true"
    :pinch-disabled="true"
    :dblclick-disabled="true"
    :mask-closable="true"
    @hide="close">
    <template v-slot:toolbar/>
  </vue-easy-lightbox>
</template>

<script lang="ts">
import VueEasyLightbox from 'vue-easy-lightbox';

const contentImageSelector = 'img:not(.img-smile)';

export function contentGallery(content: HTMLElement, event: MouseEvent): {images: string[], index: number}|null {
  const clicked = event.target as HTMLElement;
  if (!clicked.matches(contentImageSelector)) {
    return null;
  }
  const images = Array.from(content.querySelectorAll<HTMLImageElement>(contentImageSelector));
  return {
    images: images.map(image => image.src),
    index: images.indexOf(clicked as HTMLImageElement),
  };
}

export default {
  props: {
    images: {type: Array, required: true},
    index: {type: [Number, null]},
  },
  components: {
    'vue-easy-lightbox': VueEasyLightbox,
  },
  methods: {
    close(): void {
      this.$emit('close');
    },
  },
  computed: {
    lightboxImages(): object[] {
      return this.$props.images;
    },
    visible(): boolean {
      return this.$props.index !== null;
    },
  },
};
</script>
