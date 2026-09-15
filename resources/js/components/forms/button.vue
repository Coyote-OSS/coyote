<template>
  <button :disabled="disabled || processing" @click="click" :type="submit ? 'submit' : 'button'">
    <span v-if="processing || icon" class="me-1">
      <i v-if="processing" class="fa-solid fa-spinner-third fa-spin fa-fw"/>
      <vue-icon v-else :name="icon"/>
    </span>
    <slot/>
  </button>
</template>

<script>
import VueIcon from "../icon";

export default {
  components: {VueIcon},
  emits: ['click'],
  props: {
    disabled: {type: Boolean, default: false},
    processing: {type: Boolean, default: false},
    submit: {type: Boolean, default: false},
    icon: {type: String, required: false},
  },
  methods: {
    click(event) {
      this.$emit('click', event);
    },
  },
};
</script>
