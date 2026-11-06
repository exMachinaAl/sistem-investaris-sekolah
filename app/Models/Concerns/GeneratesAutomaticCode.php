<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait GeneratesAutomaticCode
{
    protected static function bootGeneratesAutomaticCode(): void
    {
        static::creating(function (Model $model): void {
            if (blank($model->getAttribute('kode'))) {
                $model->setAttribute('kode', 'TMP-'.strtoupper(Str::random(20)));
            }
        });

        static::created(function (Model $model): void {
            if (! str_starts_with((string) $model->getAttribute('kode'), 'TMP-')) {
                return;
            }

            $base = sprintf('%s-%06d', $model->automaticCodePrefix(), $model->getKey());
            $code = $base;

            while ($model->newQuery()->where('kode', $code)->whereKeyNot($model->getKey())->exists()) {
                $code = $base.'-'.strtoupper(Str::random(4));
            }

            $model->forceFill(['kode' => $code])->saveQuietly();
        });
    }

    abstract protected function automaticCodePrefix(): string;
}
