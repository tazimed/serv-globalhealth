<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $primaryKey = 'ID_Categories';
    protected $fillable = [
        'Categories'
    ];

    public function prestations()
    {
        return $this->hasMany(Prestation::class, 'ID_Categories', 'ID_Categories');
    }
}