export function string(value: string|null|undefined): string {
  if (typeof value === 'string') {
    return value;
  }
  throw new Error();
}
