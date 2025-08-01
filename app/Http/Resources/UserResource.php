<?php
namespace Coyote\Http\Resources;

use Coyote\Domain\Initials;
use Coyote\Services\Media\File;
use Coyote\Services\Parser\Factories\SigFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use function app;

/**
 * @property File $photo
 * @property string $sig
 * @property bool $allow_sig
 */
class UserResource extends JsonResource {
    public function toArray(Request $request): array {
        $parent = $this->resource->only([
            'id', 'name', 'is_online', 'bio', 'location',
            'allow_sig', 'allow_count', 'allow_smilies',
            'posts', 'location',
            'visited_at', 'created_at', 'group_name',
        ]);
        return \array_merge(
            \array_filter($parent, fn($value) => $value !== null),
            [
                'initials'    => (new Initials)->of($this->name),
                'is_verified' => $this->resource->is_verified,
                'is_incognito' => $this->resource->is_incognito,
                'is_blocked'  => $this->resource->is_blocked,
                'deleted_at'  => $this->resource->deleted_at,
                'photo'       => (string)$this->photo->url() ?: null,
            ],
            $this->isSignatureAllowed($request)
                ? ['sig' => $this->parsedSignature($this->sig)]
                : [],
        );
    }

    private function parsedSignature(string $userSig): string {
        /** @var SigFactory $signature */
        $signature = app(SigFactory::class);
        return $signature->parse($userSig);
    }

    private function isSignatureAllowed(Request $request): bool {
        return $this->sig && $this->allow_sig && (!$request->user() || $request->user()->allow_sig);
    }
}
