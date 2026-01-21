<?php

namespace App\Models\Product\Collection;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Products;

class CollectionsPage extends Model
{
    protected $table = 'collections';

    protected $fillable = [
        'name',
        'description',
        'id_tipo_collection',
        'id_product',
        'id_publicacion',
        //'id_plantilla',
        'image_url',
        'id_status_collection',
        'conditions',
        'condition_match',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'image_url' => 'string',
        'id_status_collection' => 'string',
        'conditions' => 'array',
        'condition_match' => 'string',
    ];

    /**
     * Obtener productos que cumplen con las condiciones inteligentes
     */
    public function getSmartCollectionProducts()
    {
        if ($this->id_tipo_collection != 2 || empty($this->conditions)) {
            return collect();
        }

        $query = Products::with(['variants', 'typeProduct', 'distribuidor', 'etiqueta', 'inventory'])
            ->where('id_status_product', 1);

        $conditionMatch = $this->condition_match ?? 'all';

        if ($conditionMatch === 'all') {
            // Todas las condiciones deben cumplirse (AND)
            foreach ($this->conditions as $condition) {
                $query = $this->applyCondition($query, $condition);
            }
        } else {
            // Cualquier condición puede cumplirse (OR)
            $query->where(function($q) {
                foreach ($this->conditions as $condition) {
                    $q->orWhere(function($subQuery) use ($condition) {
                        $this->applyCondition($subQuery, $condition);
                    });
                }
            });
        }

        return $query->get();
    }

    /**
     * Aplicar una condición individual al query
     */
    private function applyCondition($query, $condition)
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? 'igual';
        $value = $condition['value'] ?? '';

        if (!$field || $value === '') {
            return $query;
        }

        switch ($field) {
            case 'titulo':
                $this->applyStringCondition($query, 'name', $operator, $value);
                break;

            case 'tipo':
                $this->applyRelationCondition($query, 'id_type_product', $operator, $value);
                break;

            case 'categoria':
                $this->applyRelationCondition($query, 'id_category', $operator, $value);
                break;

            case 'proveedor':
                $this->applyRelationCondition($query, 'id_distributor', $operator, $value);
                break;

            case 'etiqueta':
                $this->applyRelationCondition($query, 'id_etiquetas', $operator, $value);
                break;

            case 'precio':
                $this->applyNumericCondition($query, 'price_unitario', $operator, $value);
                break;

            case 'precio_comparacion':
                $this->applyNumericCondition($query, 'price_comparacion', $operator, $value);
                break;

            case 'peso':
                // Buscar peso en variantes o en envío
                $query->where(function($q) use ($operator, $value) {
                    $q->whereHas('variants', function($vq) use ($operator, $value) {
                        $this->applyNumericCondition($vq, 'weight', $operator, $value);
                    });
                    // También podrías buscar en la relación de envío si existe
                });
                break;

            case 'stock':
                // Buscar stock en inventario o variantes
                $query->where(function($q) use ($operator, $value) {
                    $q->whereHas('inventory', function($iq) use ($operator, $value) {
                        $this->applyNumericCondition($iq, 'cantidad_inventario', $operator, $value);
                    })->orWhereHas('variants', function($vq) use ($operator, $value) {
                        $this->applyNumericCondition($vq, 'cantidad_inventario', $operator, $value);
                    });
                });
                break;

            case 'titulo_variante':
                $query->whereHas('variants', function($vq) use ($operator, $value) {
                    $this->applyStringCondition($vq, 'name_variant', $operator, $value);
                });
                break;
        }

        return $query;
    }

    /**
     * Aplicar condición de tipo string
     */
    private function applyStringCondition($query, $column, $operator, $value)
    {
        switch ($operator) {
            case 'igual':
                $query->where($column, '=', $value);
                break;
            case 'diferente':
                $query->where($column, '!=', $value);
                break;
            case 'contiene':
                $query->where($column, 'like', '%' . $value . '%');
                break;
            case 'no_contiene':
                $query->where($column, 'not like', '%' . $value . '%');
                break;
            case 'empieza':
                $query->where($column, 'like', $value . '%');
                break;
            case 'termina':
                $query->where($column, 'like', '%' . $value);
                break;
        }
    }

    /**
     * Aplicar condición de tipo numérico
     */
    private function applyNumericCondition($query, $column, $operator, $value)
    {
        switch ($operator) {
            case 'igual':
                $query->where($column, '=', $value);
                break;
            case 'diferente':
                $query->where($column, '!=', $value);
                break;
            case 'mayor':
                $query->where($column, '>', $value);
                break;
            case 'menor':
                $query->where($column, '<', $value);
                break;
        }
    }

    /**
     * Aplicar condición de relación (ID)
     */
    private function applyRelationCondition($query, $column, $operator, $value)
    {
        switch ($operator) {
            case 'igual':
                $query->where($column, '=', $value);
                break;
            case 'diferente':
                $query->where($column, '!=', $value);
                break;
        }
    }

    /**
     * Relación muchos a muchos con productos
     */
    public function products()
    {
        return $this->belongsToMany(Products::class, 'collection_products', 'collection_id', 'product_id')
            ->withPivot('variant_id', 'sort_order')
            ->withTimestamps()
            ->orderBy('collection_products.sort_order');
    }

    /**
     * Relación con la tabla intermedia
     */
    public function collectionProducts()
    {
        return $this->hasMany(CollectionProduct::class, 'collection_id');
    }

    /**
     * Obtener el conteo total de productos (manual o inteligente)
     */
    public function getProductsCountAttribute()
    {
        if ($this->id_tipo_collection == 1) {
            // Colección manual: contar productos en tabla intermedia
            return $this->collectionProducts()->count();
        } else {
            // Colección inteligente: contar productos que cumplen condiciones
            return $this->getSmartCollectionProducts()->count();
        }
    }

    /**
     * Obtener todos los productos (manual o inteligente)
     */
    public function getAllProducts()
    {
        if ($this->id_tipo_collection == 1) {
            // Colección manual: obtener desde tabla intermedia
            return $this->products;
        } else {
            // Colección inteligente: obtener según condiciones
            return $this->getSmartCollectionProducts();
        }
    }
}
