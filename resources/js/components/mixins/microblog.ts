import Prism from 'prismjs';

import {notify} from "../../toast";
import {confirmModal} from "../../plugins/modals";
import store from "../../store/index";
import {Microblog, User} from "../../types/models";

export const MicroblogMixin = {
  data() {
    return {
      isWrapped: false,
    };
  },
  props: {
    microblog: {
      type: Object,
      required: false,
    },
  },
  methods: {
    edit(microblog: Microblog) {
      store.commit('microblogs/TOGGLE_EDIT', microblog);

      if (microblog.is_editing) {
        this.$nextTick(() => this.$refs.form.$refs.markdown.focus());
        this.isWrapped = false;
      }
    },
    delete(action: string, microblog: Microblog) {
      confirmModal({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Usunąć wpis?',
        okLabel: 'Tak, usuń',
      })
        .then(() => store.dispatch(action, microblog));
    },
    block(user: User) {
      confirmModal({
        message: 'Nie będziesz widział komentarzy ani wpisów tego użytkownika',
        title: 'Zablokować użytkownika?',
        okLabel: 'Tak, zablokuj',
      })
        .then(() => {
          store.dispatch('user/block', user.id);
          notify({
            type: 'success',
            duration: 5000,
            title: 'Gotowe!',
            text: '<a href="javascript:" onclick="window.location.reload();">Przeładuj stronę, aby odświeżyć wyniki</a>.',
          });
        });
    },
    splice(users?: string[]): null | string {
      if (!users?.length) {
        return null;
      }

      return users.length > 10 ? users.splice(0, 10).concat('...').join("\n") : users.join("\n");
    },
  },
};

export const MicroblogFormMixin = {
  data() {
    return {
      isProcessing: false,
    };
  },
  props: {
    microblog: {
      type: Object,
      default() {
        return {
          assets: [],
          tags: [],
        };
      },
    },
  },
  methods: {
    cancel() {
      this.$emit('cancel');
    },
    save(action: string) {
      this.isProcessing = true;

      return store.dispatch(action, this.microblog)
        .then(result => {
          this.$emit('save', result.data);

          if (!this.microblog.id) {
            this.microblog.text = '';
            this.microblog.assets = [];
            this.microblog.tags = [];
          }

          // highlight once again after saving
          this.$nextTick(() => Prism.highlightAll());
        })
        .finally(() => this.isProcessing = false);
    },
  },
};
