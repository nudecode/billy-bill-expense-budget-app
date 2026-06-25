<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Frequency extends Model {
    protected $fillable = ['name', 'date_add_unit', 'date_add_value'];
}
