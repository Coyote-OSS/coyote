export interface UploadImage {
  (file: File): Promise<string>;
}
