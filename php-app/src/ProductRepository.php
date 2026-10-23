<?php

declare(strict_types=1);

namespace App;

final class ProductRepository
{
    /** Kategorie produktów - zbiór stały i znany z góry. */
    public const CATEGORIES = ['pies', 'kot', 'akwarium', 'gryzonie'];

    private const PRODUCTS = [
        1 => ['id' => 1, 'name' => 'Karma sucha dla psa 12 kg', 'category' => 'pies', 'price' => 89.90],
        2 => ['id' => 2, 'name' => 'Karma mokra dla kota 12x400 g', 'category' => 'kot', 'price' => 54.50],
        3 => ['id' => 3, 'name' => 'Żwirek dla kota 10 l', 'category' => 'kot', 'price' => 39.00],
        4 => ['id' => 4, 'name' => 'Smycz regulowana', 'category' => 'pies', 'price' => 29.90],
        5 => ['id' => 5, 'name' => 'Akwarium 60 l', 'category' => 'akwarium', 'price' => 249.00],
        6 => ['id' => 6, 'name' => 'Pokarm dla ryb 250 ml', 'category' => 'akwarium', 'price' => 12.50],
        7 => ['id' => 7, 'name' => 'Klatka dla chomika', 'category' => 'gryzonie', 'price' => 99.00],
        8 => ['id' => 8, 'name' => 'Siano dla królika 1 kg', 'category' => 'gryzonie', 'price' => 15.00],
    ];

    public function all(): array
    {
        return array_values(self::PRODUCTS);
    }

    public function find(int $id): ?array
    {
        return self::PRODUCTS[$id] ?? null;
    }
}
