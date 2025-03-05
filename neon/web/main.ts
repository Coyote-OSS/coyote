import "./tailwind.css";

window.addEventListener('DOMContentLoaded', () => {
  window.document.body.innerHTML = sectionMarkup(window.inputData.sectionName);
});

function sectionMarkup(sectionName: string): string {
  if (sectionName === 'spacing-preview') {
    return `  
      <p class="m-1">Hello</p>
      <p class="m-2">Hello</p>
      <p class="m-3">Hello</p>
      <p class="m-4">Hello</p>
    `;
  }
  if (sectionName === 'reset-elements') {
    return `
      <p>Hello</p>
      <button>Okay</button>
    `;
  }
  if (sectionName === 'responsive-elements') {
    return `
      <aside class="block md:hidden">
        aside
      </aside>
      <main>main</main>
    `;
  }
  if (sectionName === 'jobboard') {
    return `
      <h2>Oferty Pracy w IT - Odwiedza nas ponad 150 tys. programistów miesięcznie</h2>
    `;
  }
  return 'no section selected';
}
