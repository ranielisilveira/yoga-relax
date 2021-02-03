@@ -0,0 +1,41 @@
<?php

namespace App\Models;

use App\Enum\EnumMediaTypes;
use App\Models\Admin\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use SoftDeletes;

    public $fillable = [
        'category_id',
        'media',
        'type',
    ];

    public $appends = [
        'typeName'
    ];

    // Relations
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Appends
    public function getTypeNameAttribute()
    {
        return EnumMediaTypes::TYPES_ARRAY[$this->attributes['type'] ?? 0];
    }

    //Accessors
    public function getMediaAttribute()
    {
        return json_decode($this->attributes['media']);
    }
}
