<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Foto extends Model
{
    // Permite preencher o ID da pessoa e a URL da foto
    protected $fillable = [
        'pessoa_id',
        'url',
    ];

    // Define a relação inversa: A foto pertence a uma pessoa
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }
}