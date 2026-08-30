<?php

declare(strict_types=1);

namespace Modules\Publishing\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Placeholder morph target when Layout pack is not installed.
 */
class UnboundMorphModel extends Model
{
    protected $table = 'publishing_unbound_morph';

    public $timestamps = false;
}
