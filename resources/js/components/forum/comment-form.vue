<template>
  <form>
    <div v-if="!confirmedMail" class="alert alert-warning">
      Potwierdź adres e-mail, by dodać komentarz.
    </div>
    <vue-comment-autocomplete
      :source="`/completion/prompt/users/${$store.getters['topics/topic'].id}`"
      :max-length="maxLength"
      :disabled="isProcessing"
      placeholder="Drobne uwagi, sugestie. Nie odpowiadaj tutaj na pytania zawarte w poście."
      v-model="comment.text"
      @save="saveComment"
      @cancel="cancel"
      ref="commentPrompt"
    />
    <div class="row mt-1">
      <div class="col-12">
        <p class="text-muted float-start">
          Pozostało <strong>{{maxLength - comment.text.length}}</strong> znaków
        </p>
        <vue-button :disabled="isProcessing" @click="saveComment" class="btn btn-sm btn-primary neon-primary-button float-end"
                    title="Kliknij, aby wysłać (Ctrl+Enter)">
          <template v-if="newComment">Komentuj</template>
          <template v-else>Zapisz</template>
        </vue-button>
        <button @click.prevent="cancel" class="btn btn-sm btn-danger float-end me-2">Anuluj</button>
      </div>
    </div>
  </form>
</template>

<script lang="ts">
import {mapGetters} from "vuex";
import store from "../../store/index";
import VueCommentAutocomplete from '../CommentAutocomplete.vue';
import VueButton from '../forms/button.vue';

export default {
  name: 'post-comment-form',
  props: {
    comment: {type: Object, required: true},
  },
  components: {
    'vue-comment-autocomplete': VueCommentAutocomplete,
    'vue-button': VueButton,
  },
  data() {
    return {
      isProcessing: false,
      maxLength: 580,
    };
  },
  methods: {
    saveComment() {
      this.$data.isProcessing = true;
      store.dispatch('posts/saveComment', this.comment)
        .then(() => {
          this.$emit('save');
          if (!this.comment.id) {
            this.comment.text = '';
          }
        })
        .finally(() => this.$data.isProcessing = false);
    },
    focus() {
      this.$refs.commentPrompt.focus();
    },
    cancel() {
      this.$emit('cancel');
    },
  },
  computed: {
    ...mapGetters('user', ['confirmedMail']),
    newComment(): boolean {
      return typeof this.comment.id === 'undefined';
    },
  },
};
</script>
