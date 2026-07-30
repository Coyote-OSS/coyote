<?php
namespace Coyote\Modules\Campaigns\Adm\Http;

use Illuminate\Foundation\Http\FormRequest;

class VariantsUploadRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user() !== null;
    }

    public function rules(): array {
        $mimes = $this->allowedMimeTypes();
        $maxSize = $this->allowedMaxSize();
        return [
            'images'   => 'required|array|min:1',
            'images.*' => "required|image|mimes:$mimes|max:$maxSize",
        ];
    }

    public function messages(): array {
        return [
            'images.*.mimes' => 'Załączony plik musi mieć format: :values.',
        ];
    }

    private function allowedMimeTypes(): mixed {
        return config('filesystems.upload_mimes');
    }

    private function allowedMaxSize(): int {
        return (int)config('filesystems.upload_max_size') * 1024;
    }
}
