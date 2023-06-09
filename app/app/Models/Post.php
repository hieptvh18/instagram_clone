<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "captions",
        "images",
    ];

    public $timestamps = true;

    // relashionship
    public function images(){
        return $this->hasMany(PostImage::class,'post_id','id');
    }

    public function author():BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id'); 
    }

    // format timestamp
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];
}
