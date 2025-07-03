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
  document.body.classList.add('overflow-y-hidden');
  document.body.addEventListener('touchmove', preventDefault, {passive: false});
}

function enableScroll(): void {
  document.body.classList.remove('overflow-y-hidden');
  document.body.removeEventListener('touchmove', preventDefault);
}

function preventDefault(event: Event): void {
  event.preventDefault();
}
