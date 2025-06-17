import {onUnmounted} from "vue";
import {CurrentInstance, useCurrent} from "./currentInstance";

export function useClickOutside(once: boolean): ClickOutside {
  const clickOutside = new ClickOutside(once, useCurrent());
  onUnmounted(() => {
    clickOutside.removeAll();
  });
  return clickOutside;
}

type Runnable = () => void;
type Listener = () => void;

class ClickOutside {
  private removeLastHandler: Runnable|null = null;

  constructor(
    private once: boolean,
    private current: CurrentInstance) {
  }

  addClickListener(listener: Listener): void {
    this.removeLastHandler = this.onClickOutside(() => {
      listener();
      this.once && this.removeLastHandler && this.removeLastHandler();
    });
  }

  removeAll(): void {
    this.removeLastHandler && this.removeLastHandler();
  }

  private onClickOutside(clickedOutside: Listener): () => void {
    const {element, rootElement} = this.current;

    function handler(event: Event): void {
      if (!element!.contains(event.target as Node)) {
        clickedOutside();
      }
    }

    rootElement!.addEventListener('click', handler);
    return function (): void {
      rootElement!.removeEventListener('click', handler);
    };
  }
}
