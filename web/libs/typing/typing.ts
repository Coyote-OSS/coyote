export function defined<T>(value: T|undefined): T {
  if (value === undefined) {
    throw new Error('Failed to access an undefined value.');
  }
  return value;
}

export function nonNull<T>(value: T|null): T {
  if (value === null) {
    throw new Error('Failed to access a null value.');
  }
  return value;
}
