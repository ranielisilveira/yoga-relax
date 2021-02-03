<?php

namespace App\Models\Admin;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model

{
    use SoftDeletes;

    public $fillable = [
        'name',
        'color',
        'category_id'
    ];

    //Accessors
    public function getNameAttribute()
    {
        return json_decode($this->attributes['name']);
    }


    //Relations
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'category_id');
    }

    public function medias()
    {
        return $this->hasMany(Media::class, 'category_id');
    }
}
