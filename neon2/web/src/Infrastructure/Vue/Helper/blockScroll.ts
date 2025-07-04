export function useBlockScroll() {
  return function (blocked: boolean): void {
    if (blocked) {
      disableScroll();
    } else {
      enableScroll();
    }
  };
}

function disableScroll(): void {
  document.body.classList.add('overscroll-none');
  document.body.classList.add('overflow-y-hidden');
}

function enableScroll(): void {
  document.body.classList.remove('overscroll-none');
  document.body.classList.remove('overflow-y-hidden');
}
