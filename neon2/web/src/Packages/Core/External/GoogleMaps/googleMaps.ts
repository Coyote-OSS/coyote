export function googleMapsLoaded(callback: () => void): void {
  window.onGoogleMapsLoaded(() => callback());
}

declare global {
  interface Window {
    onGoogleMapsLoaded(callback: () => void): void;
  }
}
