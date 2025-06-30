<template>
  <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Inter+Tight:400,500,600,700">
  <div class="bg-body h-screen text-base">
    <div class="flex font-medium p-4 space-x-20">
      <div>
        <GuestControl/>
      </div>
      <UserControl
        username="Nazwa użytkownika"
        :has-message="false"
        :messages-count="3"/>
    </div>
  </div>
</template>

<script setup lang="ts">
import GuestControl from "./GuestControl.vue";
import UserControl from "./UserControl.vue";
</script>
