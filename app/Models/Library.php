<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Library extends Model
{
    use HasFactory;
    protected $fillable = [
'name',
'email',
'borrowed',
'borrowed_date',
'due_date',
'returned',
'return_date',
    ];
    public function library()
    {
        return $this->ManytoMany(Author::class);
    }
}
