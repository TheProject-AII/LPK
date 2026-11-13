<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigLpk extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabel terkait model ini.
     *
     * @var string
     */
    protected $table = 'config_lpk';  // Pastikan nama tabel sesuai

    /**
     * Atribut yang dapat diisi (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = ['id', 'url_android_app', 'created_at', 'updated_at'];

    /**
     * Atribut yang harus disembunyikan dalam serialisasi.
     *
     * @var array<int, string>
     */

    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Atribut yang harus dicasting.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Tambahkan casting jika diperlukan, misalnya 'created_at' => 'datetime',
    ];
}
