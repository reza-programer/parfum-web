<?php

it('semua gambar parfum demo memiliki latar putih di keempat sudut', function () {
    $files = glob(__DIR__.'/../../database/seeders/images/products/*.webp');

    expect($files)->toHaveCount(36);

    foreach ($files as $file) {
        $image = imagecreatefromwebp($file);
        expect(imagesx($image))->toBe(1200);
        expect(imagesy($image))->toBe(1200);

        foreach ([[10, 10], [1190, 10], [10, 1190], [1190, 1190]] as [$x, $y]) {
            $rgb = imagecolorsforindex($image, imagecolorat($image, $x, $y));
            foreach (['red', 'green', 'blue'] as $channel) {
                expect($rgb[$channel], basename($file)." ($x, $y) $channel")
                    ->toBeGreaterThanOrEqual(252);
            }
        }

        imagedestroy($image);
    }
});
