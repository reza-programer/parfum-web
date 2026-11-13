<?php

use App\Models\Category;

/*
 | Bug: di HP, menu hamburger hanya setinggi navbar (64px) dan halaman terkunci.
 | Akar masalah: drawer berada di dalam <nav> yang memakai backdrop-filter, sehingga
 | position: fixed dihitung terhadap navbar, bukan layar. Navbar (sticky z-30) juga
 | membuat stacking context sendiri, jadi tombol chat (z-40) bisa menutupi menu.
 | Perbaikan: drawer menu dipindah ke <body> lewat x-teleport.
 */

function navMarkup(string $html): string
{
    $start = strpos($html, '<nav');

    return substr($html, $start, strpos($html, '</nav>', $start) - $start);
}

it('drawer menu mobile dipindah ke body supaya tidak terkurung navbar', function () {
    $html = $this->get('/')->assertOk()->getContent();
    $nav = navMarkup($html);

    expect($nav)->toContain('<template x-teleport="body">')
        ->and($nav)->toContain('aria-label="Menu"');
});

it('drawer mengunci scroll lewat x-trap dan menutup diri saat link dipilih', function () {
    $nav = navMarkup($this->get('/')->getContent());

    expect($nav)->toContain('x-trap.inert.noscroll="open"')
        ->and($nav)->toContain('closest(\'a[href]\')');
});

it('link menu mobile tetap lengkap untuk tamu', function () {
    // Menu kategori dibaca dari tabel categories (header gaya NB, docs/plans/redesign-nb.md).
    foreach (['Pria', 'Wanita', 'Unisex'] as $name) {
        Category::factory()->create(['name' => $name, 'slug' => strtolower($name)]);
    }

    $nav = navMarkup($this->get('/')->getContent());

    foreach (['Pria', 'Wanita', 'Unisex', 'Brand', 'Semua parfum', 'Masuk', 'Daftar'] as $label) {
        expect($nav)->toContain($label);
    }
});
