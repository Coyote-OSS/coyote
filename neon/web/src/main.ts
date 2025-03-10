import "./style.scss";
import "./tailwind.css";
import {run} from "./application";

const shadowRoot = createShadowRoot(document.querySelector('#neon-application')!);
shadowRootCopyStyleSheets(shadowRoot);
run(shadowRoot);

function createShadowRoot(element: Element): ShadowRoot {
  const initialHtml = element.innerHTML;
  element.innerHTML = '';
  const shadowRoot = element.attachShadow({mode: 'open'});
  shadowRoot.innerHTML = initialHtml;
  return shadowRoot;
}

function shadowRootCopyStyleSheets(root: ShadowRoot): void {
  for (const styleSheet of window.document.styleSheets) {
    if (styleSheet.title === 'includeShadowRoot') {
      root.adoptedStyleSheets.push(copiedStyleSheet(styleSheet));
    }
  }
}

function copiedStyleSheet(source: CSSStyleSheet): CSSStyleSheet {
  const copy = new CSSStyleSheet();
  for (let index = 0; index < source.cssRules.length; index++) {
    copy.insertRule(source.cssRules.item(index).cssText, index);
  }
  return copy;
}
