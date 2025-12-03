<?php
// app/Models/Importador.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Importador extends Model
{
    protected $table = 'importad';
    protected $primaryKey = 'CLAVIM';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'NOMIMP', 'DNIIMP', 'EORIMP',
        'EXEIMP', 'PAGIMP',
        'TELFMP', 'TELMMP', 'CORRMP',
        'CALIMP', 'NUMIMP', 'BLO1MP', 'POR1MP',
        'NOMRAP', 'DNIRAP', 'TIREMP', 'TITUMP',
        'DENCMP', 'OBSEMP',
        'DIALMP', 'MEALMP', 'AÑALMP',
    ];

    public function getFechaAltaAttribute()
    {
        $d = (int)($this->DIALMP ?? 0);
        $m = (int)($this->MEALMP ?? 0);
        $y = (int)($this->AÑALMP ?? 0);
        return ($d && $m && $y) ? \Carbon\Carbon::create($y,$m,$d)->toDateString() : null;
    }
}
