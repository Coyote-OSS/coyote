import {ImageHosting} from "../../Application/JobBoard/Port/ImageHosting";

export class CoyoteImageHosting implements ImageHosting {
  constructor(private csrfToken: string) {}

  async uploadLogoReturnUrl(file: File): Promise<string> {
    const formData = new FormData();
    formData.append('logo', file);
    return fetch('/Firma/Logo', {
      method: 'POST',
      body: formData,
      headers: {'X-CSRF-TOKEN': this.csrfToken},
    })
      .then(response => response.json())
      .then(uploadedImage => uploadedImage.url);
  }

  async uploadAssetReturnUrl(file: File): Promise<string> {
    const formData = new FormData();
    formData.append('asset', file);
    return fetch('/assets', {
      method: 'POST',
      body: formData,
      headers: {'X-CSRF-TOKEN': this.csrfToken},
    })
      .then(response => response.json())
      .then(uploadedImage => uploadedImage.url);
  }
}
