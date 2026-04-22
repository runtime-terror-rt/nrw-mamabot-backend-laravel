<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoryImport implements ToModel, WithHeadingRow
{
    protected $imported = []; // store imported rows

    public function model(array $row)
    {
        $name = trim($row['name'] ?? '');
        if (!$name) return null;

        // Slug
        $slug = $row['slug'] ?? null;
        
        if (!$slug) {
            $base = Str::slug($name);

            // Check duplicates
            $existingCount = Category::where('slug', 'LIKE', "{$base}%")->count();

            if ($existingCount > 0) {
                // Add date for uniqueness
                $slug = $base . '-' . date('Ymd'); // e.g., furniture-20251208

                // If still duplicate, append count
                $duplicateCount = Category::where('slug', $slug)->count();
                if ($duplicateCount > 0) {
                    $slug .= '-' . ($duplicateCount + 1); // e.g., furniture-20251208-2
                }
            } else {
                $slug = $base;
            }
        } else {
            $slug = Str::slug($slug);
        }


        $status = isset($row['status']) ? (int)$row['status'] : 1;

        $image = $row['image'] ?? null;
        if ($image && !Str::startsWith($image, ['http://','https://'])) {
            $image = ltrim(str_replace(url('/') . '/storage/', '', $image), '/');
        }

        $category = new Category([
            'name' => $name,
            'slug' => $slug,
            'status' => $status,
            'image' => $image,
        ]);
        $category->save();

        // Save imported data
        $this->imported[] = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'status' => $category->status,
            'image' => $category->image ? (Str::startsWith($category->image, ['http://','https://']) ? $category->image : asset('storage/' . $category->image)) : null,
        ];

        return $category;
    }

    // ✅ THIS METHOD MUST EXIST
    public function getImported()
    {
        return $this->imported;
    }
}
