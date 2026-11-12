<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Render produk (gaya foto studio) dan konsentrasi untuk katalog contoh dari CatalogDemoSeeder.
 *
 * Gambar di database/seeders/images/products adalah render digital, bukan foto kamera, karena
 * produk dan brand-nya fiktif. Dibuat dengan database/seeders/images/source/render-products.mjs.
 * Untuk produk asli, unggah foto lewat admin; seeder ini tidak menimpa foto atau konsentrasi
 * yang sudah diisi, dan tidak menyentuh harga maupun stok.
 */
class DemoProductMediaSeeder extends Seeder
{
    /** slug => konsentrasi (nilai enum Concentration). Bisa diubah dari admin. */
    public const CONCENTRATIONS = [
        'hutan-pagi' => 'edp',
        'akar-wangi' => 'parfum',
        'kopi-tubruk' => 'edp',
        'melati-senja' => 'edp',
        'mawar-kebun' => 'edp',
        'kenanga' => 'edt',
        'hujan-pertama' => 'edt',
        'laut-biru' => 'edt',
        'teh-melati' => 'edc',
        'malam-rempah' => 'extrait',
        'gula-aren' => 'edp',
        'dupa-keraton' => 'parfum',
    ];

    public function run(): void
    {
        $source = database_path('seeders/images/products');
        $disk = Storage::disk('public');

        Product::query()->whereIn('slug', array_keys(self::CONCENTRATIONS))->get()
            ->each(function (Product $product) use ($source, $disk) {
                $updates = [];

                if ($product->concentration === null) {
                    $updates['concentration'] = self::CONCENTRATIONS[$product->slug];
                }

                if (empty($product->images)) {
                    $files = glob("{$source}/{$product->slug}-*.webp") ?: [];
                    sort($files, SORT_NATURAL);

                    $paths = [];
                    foreach ($files as $file) {
                        $target = 'products/demo/'.basename($file);

                        if (! $disk->exists($target)) {
                            $disk->put($target, file_get_contents($file));
                        }

                        $paths[] = $target;
                    }

                    if ($paths) {
                        $updates['images'] = $paths;
                    }
                }

                if ($updates) {
                    $product->update($updates);
                }
            });
    }
}
