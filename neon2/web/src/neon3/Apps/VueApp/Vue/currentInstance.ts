import {ComponentInternalInstance} from "@vue/runtime-core";
import {getCurrentInstance, onMounted} from "vue";

export function useCurrent(): CurrentInstance {
  let current = new CurrentInstance();
  onMounted(() => {
    current.set(getCurrentInstance()!);
  });
  return current;
}

export class CurrentInstance {
  public rootElement: HTMLElement|null = null;
  public element: HTMLElement|null = null;

  set(instance: ComponentInternalInstance): void {
    this.rootElement = instance.root.vnode.el as HTMLElement;
    this.element = instance.vnode.el as HTMLElement;
  }
}
