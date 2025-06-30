export interface ImageHosting {
  uploadLogoReturnUrl(file: File): Promise<string>;
  uploadAssetReturnUrl(file: File): Promise<string>;
}
