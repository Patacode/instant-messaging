<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Contact model represented by two main attributes: user_id and contact_id.
 *
 * Each user can have as many contacts as they want, and each contact link will be denoted
 * by a value pair of (user_id, contact_id) where user_id denotes the id of the user and
 * contact_id the id of his contact.
 *
 * The contact link is done in both ways (assured by the appropriate controller method when requesting the
 * database).
 */
class Contact extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'contact_id'
    ];
}

