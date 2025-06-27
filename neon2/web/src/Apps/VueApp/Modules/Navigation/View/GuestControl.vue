<template>
  <ControlTile class="space-y-4">
    <Design.Button primary class="px-10" title="Zarejestruj się" @click="register"/>
    <ControlItem title="Zaloguj się" icon="navigationNavigate" @click="login"/>
    <ControlItem title="Ciemny motyw" icon="navigationThemeDark"/>
  </ControlTile>
</template>

<script setup lang="ts">
import {inject} from "vue";
import {Design} from "../../../DesignSystem/design";
import {NavigationService} from "../NavigationService";
import ControlItem from "./Dropdown/ControlItem.vue";
import ControlTile from "./Dropdown/ControlTile.vue";
import {navigationServiceInjectKey} from "./vue";

const service = inject<NavigationService>(navigationServiceInjectKey)!;

function register(): void {
  service.attemptRegister();
}

function login(): void {
  service.attemptLogin();
}
</script>
