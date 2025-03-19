<template>
  <i :class="{'fa-fw': props.fixedWidth}"/>
</template>

<script setup lang="ts">
const props = defineProps<Props>();

interface Props {
  fixedWidth?: boolean;
}
</script>

<style>
@use "../../../node_modules/@fortawesome/fontawesome-free/css/fontawesome.css";

@font-face {
  font-family: 'Font Awesome 6 Pro';
  font-style: normal;
  font-weight: 300;
  src: url('./fa-light-300.woff2') format('woff2'), url('./fa-light-300.ttf') format('truetype');
}

@font-face {
  font-family: 'Font Awesome 6 Free';
  font-style: normal;
  font-weight: 900;
  src: url('./fa-solid-900.woff2') format('woff2'), url('./fa-solid-900.ttf') format('truetype');
}

.fa-solid {
  font-family: 'Font Awesome 6 Free';
  font-style: normal;
  font-weight: 900;
  display: inline-block;
}

.fa-light {
  font-family: 'Font Awesome 6 Pro';
  font-style: normal;
  font-weight: 300;
  display: inline-block;
}

.fa-light,
.fa-solid {
  &:before {
    content: var(--fa);
  }
}

.fa-star:before {
  content: "\f005";
}

.fa-globe-wifi:before {
  content: "\e685";
}

.fa-globe:before {
  content: "\f0ac";
}

.fa-wifi:before {
  content: "\f1eb";
}

.fa-suitcase:before {
  content: "\f0f2";
}

.fa-comment:before {
  content: "\f075";
}

.fa-location-dot:before {
  content: "\f3c5";
}

.fa-building:before {
  content: "\f1ad";
}

.fa-bars-sort:before {
  content: "\e0ae";
}

.fa-coins:before {
  content: "\f51e";
}

.fa-sack-dollar:before {
  content: "\f81d";
}

.fa-code:before {
  content: "\f121";
}

.fa-close:before {
  content: "\f00d";
}

.fa-magnifying-glass:before {
  content: "\f002";
}

.fa-laptop-code:before {
  content: "\f5fc";
}

.fa-chevron-down:before {
  content: "\f078";
}

.fa-check:before {
  content: "\f00c";
}

.fa-plus:before {
  content: "\2b";
}

.fa-tag:before {
  content: "\f02b";
}
</style>
