// Generator gambar editorial untuk beranda dan menu (render digital dari produk demo, bukan foto kamera).
// Gaya v3 (docs/plans/redesign-nb.md): latar studio polos lembut tanpa sambungan, cahaya dari kiri atas,
// botol besar dengan bayangan dan pantulan tipis. Tidak ada teks di gambar: judul dan tombol ditulis di HTML.
//
// Pemakaian (alat yang sama dengan render-products.mjs):
//   node <project>/database/seeders/images/source/render-banners.mjs <render-tools> <project>/public/images
// Hasil:
//   hero/{oriental,fresh,decant}.webp      2400x1000 (desktop, ruang kosong di kiri untuk teks)
//   hero/{oriental,fresh,decant}-m.webp    1080x1350 (HP, potret; teks di bawah gambar)
//   tiles/aroma-{woody,floral,fresh,oriental,gourmand}.webp   1000x1000
//   tiles/kategori-{pria,wanita,unisex,decant}.webp           1600x1000
import path from 'node:path';
import { products, brands, bottle, shadow, reflection, roundedPath, svg, lighten, darken, esc, createRenderer } from './render-lib.mjs';

const [toolsDir, outDir] = process.argv.slice(2);
const render = createRenderer(toolsDir);
const product = (slug) => {
    const p = products.find((item) => item.slug === slug);
    if (!p) throw new Error(`Produk tidak dikenal: ${slug}`);
    return p;
};

// ---------- vial decant 10 ml ----------
function vial(p, id, cx, bottom) {
    const w = 74, h = 270, x = cx - w / 2, top = bottom - h;
    const L = p.liquid;
    const body = roundedPath(x, top, w, h, 10, 34);
    const inner = roundedPath(x + 7, top + 46, w - 14, h - 56, 6, 28);
    return {
        w,
        markup: `
    <defs>
        <linearGradient id="${id}-liq" x1="0" x2="1"><stop offset="0" stop-color="${darken(L, 0.3)}"/><stop offset="0.45" stop-color="${lighten(L, 0.18)}"/><stop offset="1" stop-color="${darken(L, 0.32)}"/></linearGradient>
        <linearGradient id="${id}-metal" x1="0" x2="1"><stop offset="0" stop-color="#7d7d7d"/><stop offset="0.35" stop-color="#f4f4f4"/><stop offset="0.6" stop-color="#b9b9b9"/><stop offset="1" stop-color="#6c6c6c"/></linearGradient>
        <linearGradient id="${id}-glass" x1="0" x2="1"><stop offset="0" stop-color="#fff" stop-opacity="0.65"/><stop offset="0.18" stop-color="#fff" stop-opacity="0.08"/><stop offset="0.8" stop-color="#fff" stop-opacity="0.05"/><stop offset="1" stop-color="#fff" stop-opacity="0.5"/></linearGradient>
    </defs>
    <path d="${body}" fill="#ffffff" fill-opacity="0.3"/>
    <path d="${inner}" fill="url(#${id}-liq)"/>
    <rect x="${x + 7}" y="${top + 46}" width="${w - 14}" height="3" fill="#fff" opacity="0.6"/>
    <rect x="${x}" y="${top + 112}" width="${w}" height="78" fill="#fbfaf8"/>
    <rect x="${x}" y="${top + 112}" width="${w * 0.2}" height="78" fill="#fff" opacity="0.6"/>
    <text x="${cx}" y="${top + 146}" text-anchor="middle" font-family="Cormorant Garamond" font-weight="600" font-size="15" fill="#2a2a2a">${esc(p.name.split(' ')[0])}</text>
    <text x="${cx}" y="${top + 170}" text-anchor="middle" font-family="Inter" font-weight="600" font-size="9" letter-spacing="1.6" fill="#777">10 ML</text>
    <path d="${body}" fill="url(#${id}-glass)"/>
    <path d="${body}" fill="none" stroke="#fff" stroke-opacity="0.9" stroke-width="2"/>
    <path d="${body}" fill="none" stroke="#000" stroke-opacity="0.18" stroke-width="1"/>
    <rect x="${x - 3}" y="${top - 30}" width="${w + 6}" height="34" fill="url(#${id}-metal)"/>
    <rect x="${cx - 7}" y="${top - 46}" width="14" height="18" fill="url(#${id}-metal)"/>
    <path d="${roundedPath(x + 1, top - 128, w - 2, 96, 16, 4)}" fill="#ffffff" fill-opacity="0.28" stroke="#ffffff" stroke-opacity="0.85" stroke-width="2"/>
    <rect x="${x + 12}" y="${top - 118}" width="8" height="76" fill="#fff" opacity="0.55"/>`,
    };
}

// ---------- latar studio editorial: dinding dan lantai menyatu (cove), cahaya lembut ----------
function backdrop(t, W, H, floorY) {
    return `<defs>
        <linearGradient id="bd-wall" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${lighten(t.bg, 0.22)}"/><stop offset="1" stop-color="${t.bg}"/></linearGradient>
        <linearGradient id="bd-floor" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${darken(t.bg, 0.05)}"/><stop offset="1" stop-color="${lighten(t.bg, 0.12)}"/></linearGradient>
        <radialGradient id="bd-light" gradientUnits="userSpaceOnUse" cx="${W * t.lightX}" cy="${H * 0.18}" r="${Math.max(W, H) * 0.75}">
            <stop offset="0" stop-color="#fff" stop-opacity="0.5"/><stop offset="1" stop-color="#fff" stop-opacity="0"/>
        </radialGradient>
        <radialGradient id="bd-vig" cx="0.5" cy="0.5" r="0.8"><stop offset="0.65" stop-color="#000" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity="0.08"/></radialGradient>
        <filter id="bd-blur" x="0" y="-50%" width="100%" height="200%"><feGaussianBlur stdDeviation="${H * 0.03}"/></filter>
    </defs>
    <rect width="${W}" height="${H}" fill="url(#bd-wall)"/>
    <rect y="${floorY}" width="${W}" height="${H - floorY}" fill="url(#bd-floor)"/>
    <rect y="${floorY - H * 0.04}" width="${W}" height="${H * 0.08}" fill="${darken(t.bg, 0.02)}" filter="url(#bd-blur)" opacity="0.9"/>
    <rect width="${W}" height="${H}" fill="url(#bd-light)"/>
    <rect width="${W}" height="${H}" fill="url(#bd-vig)"/>`;
}

// Balok batu sederhana sebagai alas (variasi komposisi, warna senada latar).
function plinth(t, x, w, h, floorY) {
    const top = floorY - h;
    return `<defs><linearGradient id="pl-${x}" x1="0" x2="1"><stop offset="0" stop-color="${darken(t.plinth, 0.08)}"/><stop offset="0.4" stop-color="${t.plinth}"/><stop offset="1" stop-color="${darken(t.plinth, 0.14)}"/></linearGradient>
        <filter id="pls-${x}" x="-50%" y="-200%" width="200%" height="500%"><feGaussianBlur stdDeviation="18"/></filter></defs>
    <ellipse cx="${x + w * 0.55}" cy="${floorY + 6}" rx="${w * 0.62}" ry="22" fill="#000" opacity="0.14" filter="url(#pls-${x})"/>
    <rect x="${x}" y="${top}" width="${w}" height="${h}" fill="url(#pl-${x})"/>
    <rect x="${x}" y="${top}" width="${w}" height="4" fill="#fff" opacity="0.35"/>`;
}

// Satu benda di (x, bottom), skala di sekitar titik alasnya. Pantulan hanya untuk benda yang berdiri di lantai.
function place(item, idx, x, bottom, scale, W, H, withReflection) {
    const id = `i${idx}`;
    const p = product(item.slug);
    const drawn = item.vial ? vial(p, id, x, bottom) : bottle(p, id, x, bottom);
    const refl = withReflection ? reflection(id, drawn.markup, bottom, -W, W * 3, H * 3) : '';
    return `<g transform="translate(${x} ${bottom}) scale(${scale}) translate(${-x} ${-bottom})">${refl}${shadow(id, x, bottom, drawn.w, p.liquid)}${drawn.markup}</g>`;
}

/**
 * items: { slug, vial?, x (0-1 dari lebar), s (skala), lift? (pecahan tinggi), z? (urutan gambar) }
 * Benda dengan lift berdiri di atas balok yang lebarnya mengikuti lebar botol.
 */
function scene(t, W, H, floor, items) {
    const floorY = H * floor;
    const base = H / 1000;
    const things = [...items]
        .sort((a, b) => (a.z ?? 0) - (b.z ?? 0))
        .map((it, i) => {
            const x = W * it.x;
            const scale = it.s * base;
            if (!it.lift) return place(it, i, x, floorY, scale, W, H, true);
            const width = (it.vial ? 74 : brands[product(it.slug).brand].w) * scale * 1.3;
            return plinth(t, x - width / 2, width, it.lift * H, floorY) + place(it, i, x, floorY - it.lift * H, scale, W, H, false);
        })
        .join('');
    return svg(`${backdrop(t, W, H, floorY)}${things}`, W, H);
}

// Palet latar lembut per tema (bukan warna mencolok).
const tones = {
    oriental: { bg: '#ddcbbb', plinth: '#c9ad97', lightX: 0.2 },
    fresh: { bg: '#d3e0e3', plinth: '#b9ccd1', lightX: 0.2 },
    decant: { bg: '#ecdfdb', plinth: '#ddc7c1', lightX: 0.2 },
    woody: { bg: '#d8ccb9', plinth: '#c2b29a', lightX: 0.3 },
    floral: { bg: '#ecd6d4', plinth: '#dcbcb9', lightX: 0.3 },
    gourmand: { bg: '#dfcbb6', plinth: '#cbb196', lightX: 0.3 },
    pria: { bg: '#d6d3cd', plinth: '#c2bdb4', lightX: 0.3 },
    wanita: { bg: '#eedcd7', plinth: '#dec6bf', lightX: 0.3 },
    unisex: { bg: '#dcdcd2', plinth: '#c7c7ba', lightX: 0.3 },
};

const jobs = [];

// ---------- banner beranda ----------
const hero = {
    oriental: {
        desktop: [{ slug: 'dupa-keraton', x: 0.62, s: 0.95, lift: 0.12, z: 0 }, { slug: 'malam-rempah', x: 0.84, s: 1.05, z: 1 }],
        mobile: [{ slug: 'dupa-keraton', x: 0.32, s: 0.95, lift: 0.1, z: 0 }, { slug: 'malam-rempah', x: 0.66, s: 1.0, z: 1 }],
    },
    fresh: {
        desktop: [{ slug: 'teh-melati', x: 0.6, s: 0.9, z: 0 }, { slug: 'hujan-pertama', x: 0.73, s: 1.05, lift: 0.12, z: 1 }, { slug: 'laut-biru', x: 0.87, s: 0.95, z: 0 }],
        mobile: [{ slug: 'teh-melati', x: 0.24, s: 0.8, z: 0 }, { slug: 'hujan-pertama', x: 0.5, s: 0.92, lift: 0.1, z: 1 }, { slug: 'laut-biru', x: 0.77, s: 0.84, z: 0 }],
    },
    decant: {
        desktop: [
            { slug: 'melati-senja', x: 0.8, s: 1.0, z: 0 },
            { slug: 'malam-rempah', vial: true, x: 0.57, s: 1.45, z: 1 },
            { slug: 'laut-biru', vial: true, x: 0.62, s: 1.45, z: 1 },
            { slug: 'mawar-kebun', vial: true, x: 0.67, s: 1.45, z: 1 },
            { slug: 'gula-aren', vial: true, x: 0.92, s: 1.45, z: 1 },
        ],
        mobile: [
            { slug: 'melati-senja', x: 0.66, s: 0.92, z: 0 },
            { slug: 'malam-rempah', vial: true, x: 0.16, s: 1.25, z: 1 },
            { slug: 'laut-biru', vial: true, x: 0.28, s: 1.25, z: 1 },
            { slug: 'mawar-kebun', vial: true, x: 0.4, s: 1.25, z: 1 },
        ],
    },
};

for (const [key, def] of Object.entries(hero)) {
    jobs.push([scene(tones[key], 2400, 1000, 0.84, def.desktop), 2400, `hero/${key}.webp`]);
    jobs.push([scene(tones[key], 1080, 1350, 0.8, def.mobile), 1080, `hero/${key}-m.webp`]);
}

// ---------- ubin keluarga aroma (satu botol, alas balok bergantian) ----------
const aroma = {
    woody: { slug: 'hutan-pagi', block: true },
    floral: { slug: 'mawar-kebun', block: false },
    fresh: { slug: 'laut-biru', block: true },
    oriental: { slug: 'malam-rempah', block: false },
    gourmand: { slug: 'kopi-tubruk', block: true },
};
for (const [key, a] of Object.entries(aroma)) {
    const items = [{ slug: a.slug, x: 0.5, s: 1.0, lift: a.block ? 0.12 : 0 }];
    jobs.push([scene(tones[key], 1000, 1000, 0.84, items), 1000, `tiles/aroma-${key}.webp`]);
}

// ---------- ubin kategori (kelompok botol dari kategori itu, sesuai CatalogDemoSeeder) ----------
const kategori = {
    pria: [{ slug: 'laut-biru', x: 0.3, s: 0.78, z: 0 }, { slug: 'malam-rempah', x: 0.5, s: 0.88, lift: 0.1, z: 1 }, { slug: 'hutan-pagi', x: 0.7, s: 0.8, z: 0 }],
    wanita: [{ slug: 'kenanga', x: 0.3, s: 0.8, z: 0 }, { slug: 'mawar-kebun', x: 0.5, s: 0.86, lift: 0.1, z: 1 }, { slug: 'gula-aren', x: 0.7, s: 0.78, z: 0 }],
    unisex: [{ slug: 'hujan-pertama', x: 0.3, s: 0.8, z: 0 }, { slug: 'dupa-keraton', x: 0.5, s: 0.84, lift: 0.1, z: 1 }, { slug: 'akar-wangi', x: 0.7, s: 0.8, z: 0 }],
    decant: [
        { slug: 'melati-senja', x: 0.62, s: 0.82, z: 0 },
        { slug: 'malam-rempah', vial: true, x: 0.3, s: 1.2, z: 1 },
        { slug: 'laut-biru', vial: true, x: 0.37, s: 1.2, z: 1 },
        { slug: 'mawar-kebun', vial: true, x: 0.44, s: 1.2, z: 1 },
        { slug: 'gula-aren', vial: true, x: 0.8, s: 1.2, z: 1 },
    ],
};
for (const [key, items] of Object.entries(kategori)) {
    jobs.push([scene(tones[key], 1600, 1000, 0.84, items), 1600, `tiles/kategori-${key}.webp`]);
}

for (const [markup, width, file] of jobs) {
    await render(markup, width, path.join(outDir, file), 82);
}
