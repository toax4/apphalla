<?php

namespace Modules\OnlineFixScraper\Infrastructure\Models;

use App\Casts\Json;
use App\Services\Utils\StringTools;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ofs_articles';

    protected $fillable = [
        'source_id',
        'title',
        'link',
        'image',
        'sended',
        'data',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => Json::class,
    ];
}