<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmiratesId extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'document_type',
        'country_code',
        'card_number',
        'id_number',
        'date_of_birth',
        'gender',
        'expiry_date',
        'nationality',
        'surname',
        'given_names',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'date_of_birth' => 'date',
        'expiry_date' => 'date',
    ];
}
