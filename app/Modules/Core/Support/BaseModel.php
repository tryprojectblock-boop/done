<?php

declare(strict_types=1);

namespace App\Modules\Core\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected static function newFactory()
    {
        $modelClass = static::class;
        $factoryClass = str_replace('\\Models\\', '\\Database\\Factories\\', $modelClass) . 'Factory';

        if (class_exists($factoryClass)) {
            return $factoryClass::new();
        }

        return null;
    }
}
