<template>
  <ControlTile>
    <div class="text-neutral2-700 font-semibold min-w-44" v-text="props.username"/>
    <hr class="text-neutral2-200 my-3"/>
    <div class="space-y-4">
      <ControlItem title="Profil" icon="navigationProfile" @click="profile"/>
      <ControlItem @click="messages">
        Wiadomości
        <span
          class="size-4.5 rounded-xs bg-red-500 text-white text-xs flex items-center justify-center"
          v-if="props.hasMessage"
          v-text="props.messagesCount"/>
        <Icon monospace name="navigationNoMessages" v-else/>
      </ControlItem>
      <ControlItem title="Moje konto" icon="navigationAccount" @click="account"/>
      <ControlItem title="Pomoc" icon="navigationHelp" @click="help"/>
      <ControlItem title="Ciemny motyw" icon="navigationThemeDark"/>
    </div>
    <hr class="text-neutral2-200 my-3"/>
    <ControlItem title="Wyloguj" icon="navigationNavigate" @click="logout"/>
  </ControlTile>
</template>

<script setup lang="ts">
import Icon from "../../Icon/Icon.vue";
import ControlItem from "./Component/Dropdown/ControlItem.vue";
import ControlTile from "./Component/Dropdown/ControlTile.vue";
import {useNavigationService} from "./vue";

const props = defineProps<Props>();

interface Props {
  username: string;
  hasMessage: boolean;
  messagesCount?: number;
}

const service = useNavigationService();

function help(): void {
  service.attemptHelp();
}

function logout(): void {
  service.attemptLogout();
}

function messages(): void {
  service.attemptMessages();
}

function account(): void {
  service.attemptAccount();
}

function profile(): void {
  service.attemptProfile();
}
</script>
