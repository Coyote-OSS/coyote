<template>
  <i/>
</template>

<style lang="scss">
@use "@fortawesome/fontawesome-free/css/fontawesome.css";
@use "@fortawesome/fontawesome-free/css/solid.css";

@font-face {
  font-family: 'Font Awesome 6 Pro';
  font-style:  normal;
  font-weight: 300;
  src:         url(../font/fontAwesome/fa-light-300.woff2) format('woff2');
}

.fa-light {
  font-family: 'Font Awesome 6 Pro';
  font-style:  normal;
  font-weight: 300;
  display:     inline-block;
}

.fa-star:before {
  content: "\f005";
}
</style>
