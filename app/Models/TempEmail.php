<?php
namespace Coyote;

use Coyote\Domain\TempEmail\TempEmailCategory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $domain
 * @property string $category
 */
class TempEmail extends Model {
    public $timestamps = false;
    protected $fillable = ['domain', 'category'];

    public static function from(string $domain, TempEmailCategory $category): self {
        return new self([
            'domain'   => $domain,
            'category' => $category->name,
        ]);
    }
}
