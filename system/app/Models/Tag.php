<?php

namespace App\Models;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use App\Contracts\GetContents;
use App\ModelCollections\ContentCollection;

class Tag extends BaseModel implements GetContents
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'tags';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'tag';

    /**
     * 主键“类型”。
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 指示模型主键是否递增
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'tag',
        'is_preset',
        'is_show',
        'original_tag',
        'langcode',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preset' => 'boolean',
        'is_show' => 'boolean',
    ];

    public static function createIfNotExist(array $tags, $langcode = null)
    {
        $langcode = $langcode ?: langcode('content');
        $currentTags = Tag::all()->keyBy('tag');
        $count = 0;

        DB::beginTransaction();

        foreach ($tags as $tag) {
            if (! $currentTags->has($tag)) {
                static::create([
                    'tag' => $tag,
                    'original_tag' => $tag,
                    'langcode' => $langcode,
                ]);
                $count++;
            }
        }

        DB::commit();

        return $count;
    }

    public function contents($langcode = null)
    {
        if ($langcode) {
            return $this->belongsToMany(Content::class, 'content_tag', 'tag', 'content_id')
                ->wherePivot('langcode', $langcode);
        }
        return $this->belongsToMany(Content::class, 'content_tag', 'tag', 'content_id')
            ->withPivot('langcode');
    }

    public static function allTags($langcode = null)
    {
        if ($langcode) {
            return static::where('langcode', $langcode)->get()->pluck('tag')->all();
        }
        return static::all()->pluck('tag')->all();
    }

    public function getRightTag($langcode = null)
    {
        if ($this->langcode === $langcode) {
            return $this->attributes['tag'];
        }

        $tag = Tag::where('original_tag', $this->attributes['original_tag'])->get()
                ->pluck('tag', 'langcode')->all();

        if ($langcode) {
            return $tag[$langcode] ?? $this->attributes['tag'];
        }

        return $tag;
    }

    public static function saveChange(array $changes)
    {
        $tags = Tag::findMany(array_keys($changes))->keyBy('tag')->all();

        $prepareDelete = [];
        $prepareCreate = [];

        foreach ($changes as $key => $value) {
            $tag = $tags[$key] ?? null;
            if ($tag) {
                if ($value) {
                    $tag->is_show = $value['is_show'];
                    $tag->original_tag = $value['original_tag'];
                    $tag->save();
                } else {
                    $prepareDelete[] = $key;
                }
            } elseif ($value) {
                $time = Date::createFromTimestampMs($value['updated_at']);
                $prepareCreate[] = array_replace($value, [
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);
            }
        }

        if ($prepareDelete) {
            DB::table('tags')->whereIn('tag', $prepareDelete)->delete();
            DB::table('content_tag')->whereIn('tag', $prepareDelete)->delete();
        }

        if ($prepareCreate) {
            foreach ($prepareCreate as $tag) {
                DB::table('tags')->insert($tag);
            }
        }
    }

    public function get_contents(): ContentCollection
    {
        $ids = $this->contents()->pluck('id')->unique()->all();
        return ContentCollection::find($ids);
    }
}
