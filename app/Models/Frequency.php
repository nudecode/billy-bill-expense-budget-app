<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Frequency extends Model {
    use HasFactory;
    protected $fillable = ['name', 'date_add_unit', 'date_add_value'];
}
