import axios from 'axios';
import {csrfToken} from './csrfToken';

export function sendBeacon(url: string): void {
  if (navigator.sendBeacon) {
    const body = new FormData();
    body.append('_token', csrfToken());
    navigator.sendBeacon(url, body);
  } else {
    axios.post(url);
  }
}
