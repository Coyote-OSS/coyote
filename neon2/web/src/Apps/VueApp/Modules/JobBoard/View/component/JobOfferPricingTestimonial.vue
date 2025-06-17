<template>
  <div>
    <p class="text-center text-neutral2-500 text-lg mb-6">
      Pomogliśmy z sukcesami między innymi takim firmom jak:
    </p>
    <div class="grid grid-cols-3 lg:grid-cols-5">
      <img
        v-for="logo in logos"
        :src="logo.url"
        class="px-12"
        :class="{'dark:invert': logo.invertOnDark}"/>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Logo {
  url: string;
  invertOnDark?: boolean;
}

const logos: Logo[] = [
  {url: "/img/jobBoard/logo1.png"},
  {url: "/img/jobBoard/logo2.png"},
  {url: "/img/jobBoard/logo3.png", invertOnDark: true},
  {url: "/img/jobBoard/logo4.png"},
  {url: "/img/jobBoard/logo5.png", invertOnDark: true},
  {url: "/img/jobBoard/logo6.png", invertOnDark: true},
  {url: "/img/jobBoard/logo7.png", invertOnDark: true},
  {url: "/img/jobBoard/logo8.png", invertOnDark: true},
  {url: "/img/jobBoard/logo9.png", invertOnDark: true},
  {url: "/img/jobBoard/logo10.png", invertOnDark: true},
];
</script>
