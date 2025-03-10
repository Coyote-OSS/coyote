<template>
  <i :class="{'fa-fw': props.fixedWidth}"/>
</template>

<script setup lang="ts">
const props = defineProps<Props>();

interface Props {
  fixedWidth?: boolean;
}
</script>

<style lang="scss">
@use "@fortawesome/fontawesome-free/css/fontawesome.css";

@font-face {
  font-family: 'Font Awesome 6 Pro';
  font-style:  normal;
  font-weight: 300;
  src:         url(../font/fontAwesome/fa-light-300.woff2) format('woff2');
}

@font-face {
  font-family: 'Font Awesome 6 Free';
  font-style:  normal;
  font-weight: 900;
  src:         url(../font/fontAwesome/fa-solid-900.woff2) format('woff2');
}

.fa-solid {
  font-family: 'Font Awesome 6 Free';
  font-style:  normal;
  font-weight: 900;
  display:     inline-block;
}

.fa-light {
  font-family: 'Font Awesome 6 Pro';
  font-style:  normal;
  font-weight: 300;
  display:     inline-block;
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
</style>
