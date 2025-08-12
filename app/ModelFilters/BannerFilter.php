<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class BannerFilter extends ModelFilter
{
    public function code($code)
    {
        return $this->where('code', $code);
    }
}
