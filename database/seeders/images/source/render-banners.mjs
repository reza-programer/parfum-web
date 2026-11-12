// Generator banner beranda dan ikon kategori Decant (render digital dari produk demo, bukan foto).
// Teks banner TIDAK ada di gambar: judul dan tombol ditulis di HTML (resources/views/components/shop/banner-carousel.blade.php),
// jadi sisi teks (kiri) sengaja dibuat polos dan cukup gelap/terang untuk kontras.
//
// Pemakaian (alat yang sama dengan render-products.mjs):
//   node <project>/database/seeders/images/source/render-banners.mjs <render-tools> <project>/public/images
// Hasil:
//   banners/{slug}.webp (2400x800, desktop 3:1) dan banners/{slug}-m.webp (1200x900, HP 4:3)
//   categories/decant.webp (480x480)
import path from 'node:path';
import { products, bottle, shadow, roundedPath, svg, lighten, darken, esc, createRenderer } from './render-lib.mjs';

const [toolsDir, outDir] = process.argv.slice(2);
const render = createRenderer(toolsDir);
const product = (slug) => products.find((p) => p.slug === slug);

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
    <rect x="${x - 3}" y="${top - 30}" width="${w + 6}" height="34" rx="4" fill="url(#${id}-metal)"/>
    <rect x="${cx - 7}" y="${top - 46}" width="14" height="18" rx="2" fill="url(#${id}-metal)"/>
    <path d="${roundedPath(x + 1, top - 128, w - 2, 96, 16, 4)}" fill="#ffffff" fill-opacity="0.28" stroke="#ffffff" stroke-opacity="0.85" stroke-width="2"/>
    <rect x="${x + 12}" y="${top - 118}" width="8" height="76" rx="4" fill="#fff" opacity="0.55"/>`,
    };
}

// ---------- adegan studio: dinding berwarna, lengkung, podium ----------
function studio(t, W, H, stage) {
    const { cx, top, rx, ry, depth } = stage;
    const archW = rx * 1.5, archTop = top - H * 0.62;
    const arch = `M${cx - archW / 2},${top} V${archTop + archW / 2} A${archW / 2},${archW / 2} 0 0 1 ${cx + archW / 2},${archTop + archW / 2} V${top} Z`;
    const dots = t.dots.map(([fx, fy, r, o]) => `<circle cx="${fx * W}" cy="${fy * H}" r="${r * H}" fill="${t.dot}" opacity="${o}"/>`).join('');
    return `<defs>
        <linearGradient id="st-wall" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="${t.wallText}"/><stop offset="0.45" stop-color="${t.wallText}"/><stop offset="1" stop-color="${t.wall}"/></linearGradient>
        <radialGradient id="st-light" gradientUnits="userSpaceOnUse" cx="${cx}" cy="${top - H * 0.4}" r="${H * 0.95}"><stop offset="0" stop-color="#fff" stop-opacity="${t.light}"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>
        <linearGradient id="st-floor" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${t.floor}"/><stop offset="1" stop-color="${darken(t.floor, 0.12)}"/></linearGradient>
        <linearGradient id="st-arch" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${t.arch}"/><stop offset="1" stop-color="${darken(t.arch, 0.08)}"/></linearGradient>
        <linearGradient id="st-side" x1="0" x2="1"><stop offset="0" stop-color="${darken(t.podium, 0.16)}"/><stop offset="0.45" stop-color="${darken(t.podium, 0.04)}"/><stop offset="1" stop-color="${darken(t.podium, 0.2)}"/></linearGradient>
        <filter id="st-blur" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="${H * 0.03}"/></filter>
    </defs>
    <rect width="${W}" height="${H}" fill="url(#st-wall)"/>
    <rect y="${top + depth * 0.6}" width="${W}" height="${H}" fill="url(#st-floor)"/>
    ${dots}
    <path d="${arch}" fill="url(#st-arch)"/>
    <rect width="${W}" height="${H}" fill="url(#st-light)"/>
    <ellipse cx="${cx + rx * 0.12}" cy="${top + depth + ry * 0.9}" rx="${rx * 1.08}" ry="${ry * 0.9}" fill="#000" opacity="0.22" filter="url(#st-blur)"/>
    <path d="M${cx - rx},${top} V${top + depth} A${rx},${ry} 0 0 0 ${cx + rx},${top + depth} V${top} Z" fill="url(#st-side)"/>
    <ellipse cx="${cx}" cy="${top}" rx="${rx}" ry="${ry}" fill="${t.podium}"/>
    <ellipse cx="${cx}" cy="${top}" rx="${rx}" ry="${ry}" fill="none" stroke="#fff" stroke-opacity="0.35" stroke-width="2"/>`;
}

// Satu benda (botol atau vial) di atas podium. Skala lokal di sekitar titik alasnya.
function place(item, idx, x, bottom, scale) {
    const id = `i${idx}`;
    const drawn = item.vial ? vial(product(item.slug), id, x, bottom) : bottle(product(item.slug), id, x, bottom);
    const liquid = product(item.slug).liquid;
    return `<g transform="translate(${x} ${bottom}) scale(${scale}) translate(${-x} ${-bottom})">${shadow(id, x, bottom, drawn.w, liquid)}${drawn.markup}</g>`;
}

// Posisi relatif terhadap podium: dx dalam satuan rx, dy dalam satuan ry (negatif = lebih ke belakang).
function scene(t, W, H, stage, items) {
    const sorted = [...items].sort((a, b) => a.dy - b.dy);
    const things = sorted.map((it, i) => place(it, i, stage.cx + it.dx * stage.rx, stage.top + it.dy * stage.ry, it.s * stage.scale)).join('');
    return svg(`${studio(t, W, H, stage)}${things}`, W, H);
}

const themes = {
    oriental: {
        wallText: '#8f3b1f', wall: '#c4683d', arch: '#d98457', floor: '#7a321b', podium: '#f1dccb', dot: '#ffd7a8', light: 0.28,
        dots: [[0.62, 0.18, 0.025, 0.35], [0.95, 0.72, 0.04, 0.25], [0.56, 0.8, 0.018, 0.3]],
    },
    fresh: {
        wallText: '#1f6178', wall: '#4fa2b9', arch: '#79c2d3', floor: '#1b5568', podium: '#f3f8f9', dot: '#e3f6fb', light: 0.3,
        dots: [[0.6, 0.22, 0.03, 0.3], [0.96, 0.18, 0.02, 0.4], [0.58, 0.78, 0.022, 0.25]],
    },
    decant: {
        wallText: '#fde4ea', wall: '#f7c3cf', arch: '#fbd7df', floor: '#eaa4b4', podium: '#ffffff', dot: '#e11d48', light: 0.45,
        dots: [[0.6, 0.2, 0.014, 0.18], [0.95, 0.75, 0.02, 0.14], [0.57, 0.74, 0.01, 0.2]],
    },
};

const compositions = {
    oriental: [
        { slug: 'dupa-keraton', dx: -0.42, dy: -0.25, s: 0.92 },
        { slug: 'malam-rempah', dx: 0.36, dy: 0.15, s: 1 },
    ],
    fresh: [
        { slug: 'teh-melati', dx: -0.62, dy: -0.2, s: 0.86 },
        { slug: 'laut-biru', dx: 0.6, dy: -0.25, s: 0.9 },
        { slug: 'hujan-pertama', dx: -0.02, dy: 0.2, s: 1 },
    ],
    decant: [
        { slug: 'melati-senja', dx: 0.42, dy: -0.35, s: 0.9 },
        { slug: 'malam-rempah', vial: true, dx: -0.78, dy: -0.05, s: 1.3 },
        { slug: 'laut-biru', vial: true, dx: -0.5, dy: 0.25, s: 1.3 },
        { slug: 'mawar-kebun', vial: true, dx: -0.2, dy: 0.42, s: 1.3 },
        { slug: 'gula-aren', vial: true, dx: 0.86, dy: 0.1, s: 1.3 },
    ],
};

// Desktop 2400x800: teks di 40% kiri, podium di kanan. HP 1200x900: teks di kiri atas, podium di kanan bawah.
const stages = {
    desktop: { W: 2400, H: 800, stage: { cx: 1720, top: 640, rx: 470, ry: 62, depth: 70, scale: 0.84 } },
    mobile: { W: 1200, H: 900, stage: { cx: 870, top: 740, rx: 320, ry: 44, depth: 56, scale: 0.6 } },
};

for (const slug of Object.keys(themes)) {
    for (const [kind, { W, H, stage }] of Object.entries(stages)) {
        const file = path.join(outDir, 'banners', kind === 'desktop' ? `${slug}.webp` : `${slug}-m.webp`);
        await render(scene(themes[slug], W, H, stage, compositions[slug]), W, file, 82);
    }
}

// Ikon kategori Decant (bulat di beranda): tiga vial di latar terang.
{
    const W = 480, H = 480;
    const t = { ...themes.decant, wallText: '#fbe3e8', wall: '#fbe3e8', arch: '#fdeef1', dots: [] };
    const stage = { cx: 240, top: 412, rx: 170, ry: 22, depth: 30, scale: 0.74 };
    const items = [
        { slug: 'laut-biru', vial: true, dx: -0.5, dy: -0.2, s: 1 },
        { slug: 'malam-rempah', vial: true, dx: 0.5, dy: -0.2, s: 1 },
        { slug: 'mawar-kebun', vial: true, dx: 0, dy: 0.3, s: 1.08 },
    ];
    await render(scene(t, W, H, stage, items), W, path.join(outDir, 'categories', 'decant.webp'), 84);
}
