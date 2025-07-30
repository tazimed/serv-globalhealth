<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table = 'roles';
    protected $primaryKey = 'ID_Role';
    protected $fillable = [
        'Role'
    ];
    public $timestamps = false;

    public function users()
    {
        return $this->hasMany(User::class, 'ID_Role', 'ID_Role');
    }

    public function droits()
    {
        return $this->hasMany(Droit::class, 'ID_Role', 'ID_Role');
    }
}