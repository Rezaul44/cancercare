<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * নির্দিষ্ট group-এর সব settings রো একটি flat associative array-তে মার্জ করে।
     * এখন প্রতি group-এ ঠিক একটি রো আছে, কিন্তু ভবিষ্যতে একাধিক key/group সাপোর্ট করার
     * জন্য এভাবে সাধারণীকরণ করা হলো।
     *
     * @return array<string, mixed>
     */
    public static function group(string $group): array
    {
        return static::query()
            ->where('group', $group)
            ->pluck('value')
            ->reduce(fn (array $carry, array $value): array => array_merge($carry, $value), []);
    }
}
