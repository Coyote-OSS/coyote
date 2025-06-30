export function simplifiedUrl(url: string): string {
  return removePrefix(urlHostname(url), 'www.');
}

function urlHostname(url: string): string {
  try {
    return new URL(url).hostname;
  } catch (error) {
    throw new Error('Failed to simplify an invalid url.');
  }
}

function removePrefix(string: string, prefix: string): string {
  if (string.startsWith(prefix)) {
    return string.substring(prefix.length);
  }
  return string;
}
