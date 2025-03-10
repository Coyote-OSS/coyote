import "./style.scss";

const neonApplicationRoot = document.querySelector('#neon-application');
const initialHtml = neonApplicationRoot.innerHTML;
neonApplicationRoot.innerHTML = '';
const shadowRoot = neonApplicationRoot.attachShadow({mode: 'open'});
shadowRoot.innerHTML = initialHtml;
render(shadowRoot, window['backendInput']);

interface BackendIntegration {
  jobOffers: JobOffer[];
}

type JobOffer = string;

function render(root: ShadowRoot, input: BackendIntegration): void {
  const jobOffers = root.querySelector('#jobOffers')!;
  jobOffers.textContent = input.jobOffers.join('\n');
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
