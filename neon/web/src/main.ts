import "./style.scss";

const shadowRoot = document
  .querySelector('#jobBoardShadowRoot')
  .shadowRoot!;
const jobBoard = shadowRoot.querySelector('#jobBoard')!;
jobBoard.textContent = JSON.stringify(window['inputData']);

for (const styleSheet of window.document.styleSheets) {
  if (styleSheet.title === 'includeShadowRoot') {
    shadowRoot.adoptedStyleSheets.push(copiedStyleSheet(styleSheet));
  }
}

function copiedStyleSheet(source: CSSStyleSheet): CSSStyleSheet {
  const copy = new CSSStyleSheet();
  for (const rule of source.cssRules) {
    copy.insertRule(rule.cssText);
  }
  return copy;
}
