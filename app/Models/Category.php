<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'parent_category_id','image'];

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    public function childCategories()
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }

    // Recursive relationship for unlimited child categories
    public function descendants()
    {
        return $this->hasMany(Category::class, 'parent_category_id')->with('descendants');
    }

    public function ancestors()
    {
        return $this->belongsTo(Category::class, 'parent_category_id')->with('ancestors');
    }
}
