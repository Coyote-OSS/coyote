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

.fa-solid {
  font-family: 'Font Awesome 6 Free';
  font-style: normal;
  font-weight: 900;
  display: inline-block;
}

.fa-regular {
  font-family: 'Font Awesome 6 Pro';
  font-style: normal;
  font-weight: 400;
  display: inline-block;
}

.fa-light {
  font-family: 'Font Awesome 6 Pro';
  font-style: normal;
  font-weight: 300;
  display: inline-block;
}

.fa-light,
.fa-regular,
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

.fa-chart-line:before {
  content: "\f201";
}

.fa-filter:before {
  content: "\f0b0";
}

.fa-circle-info:before {
  content: "\f05a";
}

.fa-circle-plus:before {
  content: "\f055";
}

.fa-link:before {
  content: "\f0c1";
}

.fa-calendar:before {
  content: "\f133";
}

.fa-users:before {
  content: "\f0c0";
}

.fa-arrow-left:before {
  content: "\f060";
}

.fa-trash:before {
  content: "\f1f8";
}

.fa-asterisk:before {
  content: "\2a";
}

.fa-bold:before {
  content: "\f032";
}

.fa-list-ul:before {
  content: "\f0ca";
}

.fa-list-ol:before {
  content: "\f0cb";
}

.fa-spinner-third:before {
  content: "\f3f4";
}

.fa-arrow-up-right-from-square::before {
  content: "\f08e";
}
</style>
