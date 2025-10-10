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

    // opcional: define $fillable si vas a actualizar campos concretos
    // protected $fillable = ['NOMIMP', 'DNIIMP', ...];

    // ejemplo de accesor para fechas D/M/A si las necesitas compuestas
    public function getFechaAltaAttribute()
    {
        $d = (int)($this->DIALMP ?? 0);
        $m = (int)($this->MEALMP ?? 0);
        $y = (int)($this->AÑALMP ?? 0);
        return ($d && $m && $y) ? \Carbon\Carbon::create($y,$m,$d)->toDateString() : null;
    }
}
