<template>
  <ControlTile class="space-y-4">
    <Design.Button primary class="px-10" title="Zarejestruj się" @click="register"/>
    <ControlItem title="Zaloguj się" icon="navigationNavigate" @click="login"/>
    <ControlItem title="Ciemny motyw" icon="navigationThemeDark"/>
  </ControlTile>
</template>

<script setup lang="ts">
import {Design} from "../../DesignSystem/design";
import ControlItem from "./Dropdown/ControlItem.vue";
import ControlTile from "./Dropdown/ControlTile.vue";
import {useNavigationService} from "./vue";

const service = useNavigationService();

function register(): void {
  service.attemptRegister();
}

function login(): void {
  service.attemptLogin();
}
</script>
