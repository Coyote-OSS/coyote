export function request(method: string, url: string, body: object): Promise<Response> {
  return fetch(url, {
    method,
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(body),
  });
}

export function requestGet(url: string, queryParams: Record<string, string>): Promise<Response> {
  return fetch(url + '?' + urlQueryParams(queryParams));
}

function urlQueryParams(params: Record<string, string>): string {
  return new URLSearchParams(params).toString();
}
