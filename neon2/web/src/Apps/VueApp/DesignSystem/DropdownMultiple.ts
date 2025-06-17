export function clamp(value: number, min: number, max: number): number {
  return Math.max(Math.min(max, value), min);
}
