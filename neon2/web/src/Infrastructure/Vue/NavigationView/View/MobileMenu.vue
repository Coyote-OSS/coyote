<template>
  <div class="h-screen text-xl p-4 pb-6 flex flex-col">
    <div class="flex justify-between">
      <BrandLogo/>
      <Icon name="mobileMenuClose"/>
    </div>
    <div v-for="category in menu">
      {{category.title}}
    </div>
    <div class="mt-auto">
      <Design.Button primary class="w-full">Zarejestruj się</Design.Button>
    </div>
    <div class="mt-3">
      <Design.Button primary-outline class="w-full">Zaloguj</Design.Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import {Design} from "../../DesignSystem/design";
import Icon from "../../Icon/Icon.vue";
import BrandLogo from "./BrandLogo.vue";

const menu = [
  {title: 'Dla kandydatów'},
  {title: 'Dla pracodawców'},
  {title: 'Forum'},
];
</script>
