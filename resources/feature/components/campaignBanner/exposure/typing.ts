export function asHtmlImageElement(element: Element): HTMLImageElement {
  if (element instanceof HTMLImageElement) {
    return element;
  }
  throw new Error();
}
