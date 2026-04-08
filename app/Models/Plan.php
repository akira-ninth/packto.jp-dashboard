<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'name', 'features'])]
class Plan extends Model
{
    protected function casts(): array
    {
        return [
            'features' => 'array',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * 機能フラグ判定 (Cloudflare worker 側の planAllows と同じセマンティクス)
     */
    public function allows(string $feature): bool
    {
        return ($this->features[$feature] ?? false) === true;
    }
}
