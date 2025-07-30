<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestation extends Model
{
    use HasFactory;
    protected $table = 'prestations';
    protected $primaryKey = 'ID_Prestation';
    protected $fillable = [
        'Prestations', 'Durees', 'Prix', 'ID_Categories'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'ID_Categories', 'ID_Categories');
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'ID_Prestation', 'ID_Prestation');
    }
}