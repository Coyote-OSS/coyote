import "./style.scss";

const jobBoardRoot = document.querySelector('#jobBoardRoot');
const initialHtml = jobBoardRoot.innerHTML;
jobBoardRoot.innerHTML = '';
const shadowRoot = jobBoardRoot.attachShadow({mode: 'open'});
shadowRoot.innerHTML = initialHtml;
render(shadowRoot, window['inputData']);

interface BackendIntegration {
  jobOffers: JobOffer[];
}

type JobOffer = string;

function render(root: ShadowRoot, input: BackendIntegration): void {
  const jobBoard = root.querySelector('#jobBoard')!;
  jobBoard.textContent = input.jobOffers.join('\n');
}

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
