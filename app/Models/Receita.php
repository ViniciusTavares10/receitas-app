<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receita extends Model
{
    use HasFactory;

    public const STATUS_ATIVO = 'ATIVO';

    public const STATUS_INATIVO = 'INATIVO';

    protected $table = 'receita';

    protected $fillable = [
        'nome',
        'descricao',
        'data_registro',
        'custo',
        'tipo_receita',
        'status',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'data_registro' => 'date',
            'custo' => 'decimal:2',
        ];
    }

    public static function statusOptions(): array     
    {
        return [
            self::STATUS_ATIVO => 'ATIVO',
            self::STATUS_INATIVO => 'INATIVO',
        ];
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                filled($filters['status'] ?? null),
                fn (Builder $builder) => $builder->where('status', $filters['status'])
            )
            ->when(
                filled($filters['data_inicial'] ?? null),
                fn (Builder $builder) => $builder->whereDate('data_registro', '>=', $filters['data_inicial'])
            )
            ->when(
                filled($filters['data_final'] ?? null),
                fn (Builder $builder) => $builder->whereDate('data_registro', '<=', $filters['data_final'])
            );
    }
}
