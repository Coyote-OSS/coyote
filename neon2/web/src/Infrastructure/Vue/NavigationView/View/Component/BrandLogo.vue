<template>
  <a href="/">
    <img :src="logoLight" alt="4programmers.net" class="h-6.5 dark:hidden"/>
    <img :src="logoDark" alt="4programmers.net" class="h-6.5 hidden dark:block"/>
  </a>
</template>

<script setup lang="ts">
import logoDark from "../../../../../../../../public/img/logo.dark.svg";
import logoLight from "../../../../../../../../public/img/logo.light.svg";
</script>
