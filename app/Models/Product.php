<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $appends = ['product_url'];

    protected $fillable = [
        'name', 'title', 'price',
        'quantity', 'title', 'description', 'categorie_id', 'image'
    ];

    protected function getProductUrlAttribute()
    {
        $urls = ['https://images.unsplash.com/photo-1523275335684-37898b6baf30?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8cHJvZHVjdHxlbnwwfHwwfHx8MA%3D%3D'];
        if ($this->image) {
            $images = json_decode($this->image, true);
            $urls = [];
            foreach ($images as $value) {
                $cardMedia = Media::find($value);
                if ($cardMedia) {
                    $cardUrl = $cardMedia->getFullUrl();
                    array_push($urls, $cardUrl);
                }
            }
        }
        return $urls;
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'categorie_id');
    }
}
