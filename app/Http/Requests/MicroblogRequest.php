<?php
namespace Coyote\Http\Requests;

use Coyote\Repositories\Contracts\TagRepositoryInterface;
use Coyote\Rules\TagDeleted;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MicroblogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id'   => ['nullable', 'integer', Rule::exists('microblogs', 'id')->whereNull('deleted_at')],
            'text'        => 'required|string|max:65535',
            'tags'        => 'array|max:5',
            'tags.*.name' => [
                'bail',
                'max:25',
                'tag',
                new TagDeleted($this->container[TagRepositoryInterface::class]),
                'tag_creation:300',
            ],
        ];
    }
}
