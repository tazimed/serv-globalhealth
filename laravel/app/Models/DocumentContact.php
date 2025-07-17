<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentContact extends Model
{
    use HasFactory;
    protected $table = 'document_contacts';
    protected $primaryKey = 'Id_Document_contact';
    protected $fillable = [
        'Nom_Doc', 'Doc', 'ID_Contact'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'ID_Contact', 'ID_Contact');
    }
}