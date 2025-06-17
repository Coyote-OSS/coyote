import {defineStore} from 'pinia';

export const useBoardStore = defineStore('jobBoard', {
  state(): State {
    return {
    };
  },
});

interface State {
}


export type BoardStore = ReturnType<typeof useBoardStore>;
