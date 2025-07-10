<template>
  <div class="bg-tile pt-8 mt-6">
    <div class="max-w-400 mx-auto px-3">
      <div class="lg:flex lg:justify-between">
        <div class="lg:flex lg:flex-col">
          <div class="mb-8 brand-logo"></div>
          <div class="max-lg:mb-8 max-lg:grid xs:grid-cols-2 md:grid-cols-4 gap-y-4 lg:flex lg:flex-col lg:justify-between lg:flex-grow-1">
            <div>
              <p class="text-neutral2-700">Napisz do nas</p>
              <a
                class="text-sm text-neutral2-500"
                :href="'mailto:' + footerMenu.contactUsMail"
                v-text="footerMenu.contactUsMail"/>
            </div>
            <div class="text-2xl space-x-2 text-neutral2-500">
              <a :href="footerMenu.contactUsFacebookHref" target="_blank">
                <Icon name="contactUsFacebook"/>
              </a>
              <a :href="footerMenu.contactUsLinkedInHref" target="_blank">
                <Icon name="contactUsLinkedIn"/>
              </a>
              <a :href="footerMenu.contactUsGithubHref" target="_blank">
                <Icon name="contactUsGithub"/>
              </a>
            </div>
          </div>
        </div>
        <div class="grid xs:grid-cols-2 md:grid-cols-4 gap-y-8 lg:gap-x-6 xl:gap-x-16 2xl:gap-x-28">
          <div class="space-y-3" v-for="section in footerMenu.sections">
            <p class="text-neutral2-600 text-lg font-semibold" v-text="section.title"/>
            <a
              v-for="item in section.items"
              class="block text-sm text-neutral2-500"
              :href="item.href"
              v-text="item.title"/>
          </div>
        </div>
      </div>
      <div class="mt-8 pb-4 text-xs text-neutral2-700">
        Copyright &copy; 2000 - {{footerMenu.copyrightYear}} 4programmers, {{footerMenu.executionTime}}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Icon from "../../../Icon/Icon.vue";

const footerMenu = window.backendInput.footerMenu;
</script>
