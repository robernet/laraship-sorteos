<?php

namespace Corals\Modules\Sorteos\Observers;

use Corals\Modules\Sorteos\Models\Sorteo;
use Illuminate\Support\Str;

class SorteoObserver
{
    public function creating(Sorteo $sorteo): void
    {
        if (empty($sorteo->slug)) {
            $sorteo->slug = $this->uniqueSlug($sorteo->name);
        }
    }

    public function updating(Sorteo $sorteo): void
    {
        if (empty($sorteo->slug)) {
            $sorteo->slug = $this->uniqueSlug($sorteo->name);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Sorteo::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
