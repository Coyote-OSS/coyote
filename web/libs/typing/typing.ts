export function defined<T>(value: T|undefined): T {
  if (value === undefined) {
    throw new Error('Failed to access an undefined value.');
  }
  return value;
}
