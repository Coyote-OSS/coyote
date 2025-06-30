<template>
  <div
    class="size-8 rounded-lg flex items-center justify-center cursor-pointer bg-tile hover:accent"
    :class="{'text-green-500': favourite}"
    @click="toggle"
    data-testid="jobOfferFavourite"
    :data-checked="favourite ? 'checked' : 'unchecked'">
    <Icon :name="favourite ? 'jobOfferFavouriteChecked' : 'jobOfferFavourite'"/>
  </div>
</template>

<script setup lang="ts">
import Icon from '../../../Icon/Icon.vue';

const favourite = defineModel<boolean>({required: true});

function toggle(): void {
  favourite.value = !favourite.value;
}
</script>
