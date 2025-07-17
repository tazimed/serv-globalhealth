<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentUser extends Model
{
    use HasFactory;
    protected $table = 'document_users';
    protected $primaryKey = 'Id_Document';
    protected $fillable = [
        'Nom_Doc', 'Doc', 'ID_User'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'ID_User', 'ID_User');
    }
}