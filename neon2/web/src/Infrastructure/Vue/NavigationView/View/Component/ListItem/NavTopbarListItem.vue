<template>
  <template v-if="props.type === 'link'">
    <div class="group/navItem" v-if="props.forumMenu">
      <a
        :class="navigationLinkStyle"
        class="group-hover/navItem:text-green2-500"
        v-text="props.title"
        @click.prevent="click"
        :href="actionHref(props.action)"/>
      <ForumMenu/>
    </div>
    <div v-else :class="navigationLinkStyle" class="whitespace-nowrap group/navItem">
      <a class="block hover:text-green2-500" v-text="props.title" @click.prevent="click" :href="actionHref(props.action)"/>
      <div v-if="includeChildren" class="relative hidden group-hover/navItem:block cursor-default">
        <div :class="[
          'absolute left-1/2 -translate-x-1/2 top-2',
          'bg-tile p-2 font-medium space-y-2',
          'border rounded-lg border-tile-border',
        ]">
          <a
            v-for="child in props.children"
            class="block hover:text-green2-500 p-2 cursor-pointer"
            v-text="child.title"
            :href="actionHref(child.action)"
            @click.prevent="action(child.action)"/>
        </div>
      </div>
    </div>
  </template>
  <template v-else-if="props.type === 'buttonSecondary'">
    <Button
      class="text-base h-10 leading-6"
      primary-outline
      :title="props.title"
      @click="click"/>
  </template>
  <template v-else-if="props.type === 'space'">
    <div class="mr-auto"/>
  </template>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Button from "../../../../DesignSystem/Button.vue";
import {NavigationAction} from "../../../Port/NavigationService";
import {useNavigationStore} from "../../navigationStore";
import {NavigationItem} from "../../Presenter/entryPointItems";
import {useNavigationService} from "../../vue";
import ForumMenu from "../ForumMenuDesktop.vue";

const props = defineProps<Props>();
const emit = defineEmits(['click']);

const store = useNavigationStore();
const service = useNavigationService();

const navigationLinkStyle = 'max-lg:hidden cursor-pointer py-2 px-1 xl:px-3 rounded';

interface Emit {
  (event: 'action', action: NavigationAction): void;
}

interface Props {
  type: 'link'|'buttonSecondary'|'space';
  title?: string;
  forumMenu?: boolean;
  action?: NavigationAction;
  children?: NavigationItem[];
  childrenForNotAuthenticated?: boolean;
}

function action(action: NavigationAction): void {
  emit('action', action);
}

function actionHref(action: NavigationAction): string|null {
  return service.actionHref(action);
}

function click(): void {
  action(props.action);
}

const includeChildren = computed<boolean>(() => {
  if (props.childrenForNotAuthenticated && store.isAuthenticated) {
    return false;
  }
  return props.children?.length > 0;
});
</script>
