// Pustaka render bersama untuk gambar demo Toko Parfum: botol, bayangan, kotak kemasan, latar studio.
// Dipakai render-products.mjs (foto produk) dan render-banners.mjs (banner beranda).
// Semua hasil adalah render digital dari produk fiktif CatalogDemoSeeder, bukan foto kamera.
import { createRequire } from 'node:module';
import fs from 'node:fs';
import path from 'node:path';

// ---------- warna ----------
const hex = (h) => [1, 3, 5].map((i) => parseInt(h.slice(i, i + 2), 16));
const toHex = (rgb) => '#' + rgb.map((v) => Math.max(0, Math.min(255, Math.round(v))).toString(16).padStart(2, '0')).join('');
const mix = (a, b, t) => toHex(hex(a).map((v, i) => v + (hex(b)[i] - v) * t));
const lighten = (c, t) => mix(c, '#ffffff', t);
const darken = (c, t) => mix(c, '#000000', t);

// ---------- data produk (fiktif, sama dengan CatalogDemoSeeder) ----------
const CONC = { extrait: 'EXTRAIT DE PARFUM', parfum: 'PARFUM', edp: 'EAU DE PARFUM', edt: 'EAU DE TOILETTE', edc: 'EAU DE COLOGNE' };
const products = [
    { slug: 'hutan-pagi', name: 'Hutan Pagi', brand: 'Rimba Atelier', scent: 'woody', conc: 'edp', ml: 50, liquid: '#b7a173' },
    { slug: 'akar-wangi', name: 'Akar Wangi', brand: 'Rimba Atelier', scent: 'woody', conc: 'parfum', ml: 50, liquid: '#86653a' },
    { slug: 'kopi-tubruk', name: 'Kopi Tubruk', brand: 'Rimba Atelier', scent: 'gourmand', conc: 'edp', ml: 50, liquid: '#6e4a2c' },
    { slug: 'melati-senja', name: 'Melati Senja', brand: 'Senja Parfumerie', scent: 'floral', conc: 'edp', ml: 50, liquid: '#f0d9bf' },
    { slug: 'mawar-kebun', name: 'Mawar Kebun', brand: 'Senja Parfumerie', scent: 'floral', conc: 'edp', ml: 50, liquid: '#e6b0b1' },
    { slug: 'kenanga', name: 'Kenanga', brand: 'Senja Parfumerie', scent: 'floral', conc: 'edt', ml: 50, liquid: '#eddc9b' },
    { slug: 'hujan-pertama', name: 'Hujan Pertama', brand: 'Kala Scent', scent: 'fresh', conc: 'edt', ml: 50, liquid: '#cfe2dc' },
    { slug: 'laut-biru', name: 'Laut Biru', brand: 'Kala Scent', scent: 'fresh', conc: 'edt', ml: 50, liquid: '#9fc3d6' },
    { slug: 'teh-melati', name: 'Teh Melati', brand: 'Kala Scent', scent: 'fresh', conc: 'edc', ml: 30, liquid: '#d9e0b6' },
    { slug: 'malam-rempah', name: 'Malam Rempah', brand: 'Saka & Co', scent: 'oriental', conc: 'extrait', ml: 50, liquid: '#9a4a26' },
    { slug: 'gula-aren', name: 'Gula Aren', brand: 'Saka & Co', scent: 'gourmand', conc: 'edp', ml: 50, liquid: '#c6884c' },
    { slug: 'dupa-keraton', name: 'Dupa Keraton', brand: 'Saka & Co', scent: 'oriental', conc: 'parfum', ml: 50, liquid: '#5c2b27' },
];

const backdrops = {
    woody: ['#ece7df', '#d9d1c4', '#efebe4'],
    floral: ['#f1eae7', '#e0d4cf', '#f4eeeb'],
    fresh: ['#eceeee', '#d8dfdf', '#f1f3f3'],
    oriental: ['#eee6df', '#d8cabd', '#f2ebe5'],
    gourmand: ['#efe8df', '#ddcfbf', '#f3ece4'],
};

// Bentuk botol, tutup, dan label per brand.
const brands = {
    'Rimba Atelier': { w: 300, h: 450, rt: 22, rb: 22, wall: 16, base: 46, neckW: 70, neckH: 26, cap: { w: 150, h: 150, kind: 'wood' }, label: { kind: 'kraft', w: 210, h: 170, y: 0.40 }, box: '#cdb995', boxInk: '#2b2117' },
    'Senja Parfumerie': { w: 360, h: 400, rt: 120, rb: 46, wall: 18, base: 42, neckW: 64, neckH: 30, cap: { w: 132, h: 118, kind: 'gold' }, label: { kind: 'white', w: 220, h: 150, y: 0.46 }, box: '#ead9d3', boxInk: '#3a2826' },
    'Kala Scent': { w: 270, h: 470, rt: 30, rb: 30, wall: 14, base: 40, neckW: 0, neckH: 0, cap: { w: 270, h: 112, kind: 'matte' }, label: { kind: 'print', w: 200, h: 160, y: 0.42 }, box: '#f5f4f1', boxInk: '#1d1d1b' },
    'Saka & Co': { w: 400, h: 390, rt: 30, rb: 30, wall: 22, base: 54, neckW: 78, neckH: 22, cap: { w: 176, h: 150, kind: 'black' }, label: { kind: 'black', w: 230, h: 150, y: 0.44 }, box: '#1b1917', boxInk: '#d8bd7c' },
};

const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;');

function roundedPath(x, y, w, h, rt, rb) {
    rt = Math.min(rt, w / 2, h / 2);
    rb = Math.min(rb, w / 2, h / 2);
    return `M${x + rt},${y} H${x + w - rt} Q${x + w},${y} ${x + w},${y + rt} V${y + h - rb} Q${x + w},${y + h} ${x + w - rb},${y + h} H${x + rb} Q${x},${y + h} ${x},${y + h - rb} V${y + rt} Q${x},${y} ${x + rt},${y} Z`;
}

// ---------- tutup ----------
function cap(id, kind, x, y, w, h) {
    const p = roundedPath(x, y, w, h, kind === 'gold' ? 10 : 8, 4);
    switch (kind) {
        case 'wood': {
            const grain = Array.from({ length: 9 }, (_, i) => {
                const gx = x + 12 + i * (w - 24) / 8;
                return `<path d="M${gx},${y + 6} C${gx + 6},${y + h * 0.35} ${gx - 6},${y + h * 0.65} ${gx + 3},${y + h - 6}" stroke="#2c1a0d" stroke-opacity="0.16" stroke-width="2" fill="none"/>`;
            }).join('');
            return `<defs><linearGradient id="${id}-cap" x1="0" x2="1"><stop offset="0" stop-color="#4b301a"/><stop offset="0.3" stop-color="#8a6140"/><stop offset="0.55" stop-color="#a77c55"/><stop offset="0.8" stop-color="#7a5434"/><stop offset="1" stop-color="#40291a"/></linearGradient></defs>
            <path d="${p}" fill="url(#${id}-cap)"/>${grain}
            <rect x="${x + w * 0.22}" y="${y + 8}" width="${w * 0.07}" height="${h - 16}" fill="#fff" opacity="0.14"/>`;
        }
        case 'gold':
            return `<defs><linearGradient id="${id}-cap" x1="0" x2="1"><stop offset="0" stop-color="#7d5f27"/><stop offset="0.18" stop-color="#c9a659"/><stop offset="0.34" stop-color="#f4e2a8"/><stop offset="0.5" stop-color="#b98f3f"/><stop offset="0.7" stop-color="#e8cf87"/><stop offset="1" stop-color="#6f531f"/></linearGradient></defs>
            <path d="${p}" fill="url(#${id}-cap)"/>
            ${Array.from({ length: 14 }, (_, i) => `<rect x="${x}" y="${y + 8 + i * (h - 16) / 14}" width="${w}" height="1" fill="#fff" opacity="0.08"/>`).join('')}`;
        case 'matte':
            return `<defs><linearGradient id="${id}-cap" x1="0" x2="1"><stop offset="0" stop-color="#cfccc5"/><stop offset="0.35" stop-color="#f6f5f2"/><stop offset="0.6" stop-color="#ffffff"/><stop offset="1" stop-color="#c9c5bd"/></linearGradient></defs>
            <path d="${p}" fill="url(#${id}-cap)"/>
            <rect x="${x}" y="${y + h - 3}" width="${w}" height="3" fill="#000" opacity="0.12"/>`;
        case 'black':
        default:
            return `<defs><linearGradient id="${id}-cap" x1="0" x2="1"><stop offset="0" stop-color="#050505"/><stop offset="0.28" stop-color="#2f2f2f"/><stop offset="0.42" stop-color="#585858"/><stop offset="0.55" stop-color="#1c1c1c"/><stop offset="1" stop-color="#030303"/></linearGradient>
                <linearGradient id="${id}-ring" x1="0" x2="1"><stop offset="0" stop-color="#7d5f27"/><stop offset="0.4" stop-color="#f1dc9f"/><stop offset="1" stop-color="#7a5b22"/></linearGradient></defs>
            <path d="${p}" fill="url(#${id}-cap)"/>
            <rect x="${x + w * 0.36}" y="${y + 10}" width="${w * 0.05}" height="${h - 34}" fill="#fff" opacity="0.28"/>
            <rect x="${x}" y="${y + h - 16}" width="${w}" height="9" fill="url(#${id}-ring)"/>`;
    }
}

// ---------- label ----------
function label(p, b, cx, y, w, h) {
    const x = cx - w / 2;
    const nameSize = p.name.length > 11 ? 30 : 36;
    const conc = CONC[p.conc];
    const styles = {
        kraft: { bg: '#e6dac6', edge: '#c9b89b', ink: '#2b2117', sub: '#6b5a44' },
        white: { bg: '#fbf8f5', edge: '#e2d6cf', ink: '#2c2220', sub: '#8a7470' },
        print: { bg: 'none', edge: 'none', ink: '#1e1e1c', sub: '#4b4b48' },
        black: { bg: '#121110', edge: '#000', ink: '#dfc488', sub: '#b39a62' },
    }[b.label.kind];
    const plate = styles.bg === 'none' ? '' : `<rect x="${x}" y="${y}" width="${w}" height="${h}" fill="${styles.bg}" stroke="${styles.edge}" stroke-width="1"/>
        ${b.label.kind === 'white' ? `<rect x="${x + 8}" y="${y + 8}" width="${w - 16}" height="${h - 16}" fill="none" stroke="#c9b3ab" stroke-width="0.8"/>` : ''}
        <rect x="${x}" y="${y}" width="${w * 0.18}" height="${h}" fill="#fff" opacity="${b.label.kind === 'black' ? 0.06 : 0.25}"/>`;
    return `${plate}
        <text x="${cx}" y="${y + h * 0.27}" text-anchor="middle" font-family="Inter" font-weight="600" font-size="11" letter-spacing="3.2" fill="${styles.sub}">${esc(p.brand.toUpperCase())}</text>
        <text x="${cx}" y="${y + h * 0.58}" text-anchor="middle" font-family="Cormorant Garamond" font-weight="600" font-size="${nameSize}" fill="${styles.ink}">${esc(p.name)}</text>
        <rect x="${cx - 16}" y="${y + h * 0.68}" width="32" height="1" fill="${styles.sub}" opacity="0.7"/>
        <text x="${cx}" y="${y + h * 0.82}" text-anchor="middle" font-family="Inter" font-weight="500" font-size="9.5" letter-spacing="2.4" fill="${styles.sub}">${conc} · ${p.ml} ML</text>`;
}

// ---------- botol lengkap ----------
// Mengembalikan markup botol di (cx, bottom) dan kotak label untuk crop detail.
function bottle(p, id, cx, bottom) {
    const b = brands[p.brand];
    const x = cx - b.w / 2;
    const top = bottom - b.h;
    const body = roundedPath(x, top, b.w, b.h, b.rt, b.rb);
    const ix = x + b.wall, iw = b.w - b.wall * 2, iy = top + b.wall, ih = b.h - b.wall - b.base;
    const inner = roundedPath(ix, iy, iw, ih, Math.max(4, b.rt - b.wall), Math.max(4, b.rb - b.wall * 0.6));
    const liquidTop = iy + ih * 0.16;
    const L = p.liquid;
    const neckY = top - b.neckH;
    const capY = neckY - b.cap.h + (b.neckW ? 4 : 0);
    const capX = cx - b.cap.w / 2;
    const labelY = top + b.h * b.label.y - b.label.h / 2;

    const markup = `
    <defs>
        <clipPath id="${id}-body"><path d="${body}"/></clipPath>
        <clipPath id="${id}-inner"><path d="${inner}"/></clipPath>
        <linearGradient id="${id}-liq" x1="0" x2="1">
            <stop offset="0" stop-color="${darken(L, 0.32)}"/><stop offset="0.22" stop-color="${darken(L, 0.08)}"/>
            <stop offset="0.5" stop-color="${lighten(L, 0.16)}"/><stop offset="0.78" stop-color="${darken(L, 0.06)}"/>
            <stop offset="1" stop-color="${darken(L, 0.34)}"/>
        </linearGradient>
        <linearGradient id="${id}-liqv" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#fff" stop-opacity="0.22"/><stop offset="0.5" stop-color="#fff" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity="0.18"/>
        </linearGradient>
        <linearGradient id="${id}-glass" x1="0" x2="1">
            <stop offset="0" stop-color="#ffffff" stop-opacity="0.6"/><stop offset="0.08" stop-color="#ffffff" stop-opacity="0.16"/><stop offset="0.2" stop-color="#ffffff" stop-opacity="0.02"/>
            <stop offset="0.8" stop-color="#ffffff" stop-opacity="0.02"/><stop offset="0.93" stop-color="#ffffff" stop-opacity="0.14"/><stop offset="1" stop-color="#ffffff" stop-opacity="0.55"/>
        </linearGradient>
        <linearGradient id="${id}-hl" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#fff" stop-opacity="0.85"/><stop offset="0.45" stop-color="#fff" stop-opacity="0.25"/><stop offset="1" stop-color="#fff" stop-opacity="0.6"/>
        </linearGradient>
        <filter id="${id}-soft" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="2.2"/></filter>
    </defs>

    <!-- leher -->
    ${b.neckW ? `<rect x="${cx - b.neckW / 2}" y="${neckY}" width="${b.neckW}" height="${b.neckH + 6}" fill="${lighten(L, 0.55)}" opacity="0.9"/>
    <rect x="${cx - b.neckW / 2}" y="${neckY}" width="${b.neckW}" height="${b.neckH + 6}" fill="url(#${id}-glass)"/>` : ''}

    <!-- kaca + cairan -->
    <path d="${body}" fill="#ffffff" fill-opacity="0.34"/>
    <g clip-path="url(#${id}-inner)">
        <rect x="${ix}" y="${liquidTop}" width="${iw}" height="${ih}" fill="url(#${id}-liq)" opacity="0.97"/>
        <rect x="${ix}" y="${liquidTop}" width="${iw}" height="${ih}" fill="url(#${id}-liqv)"/>
        <rect x="${ix}" y="${liquidTop}" width="${iw}" height="3" fill="#fff" opacity="0.55"/>
        <rect x="${ix}" y="${liquidTop + 3}" width="${iw}" height="10" fill="${darken(L, 0.2)}" opacity="0.25"/>
    </g>
    <path d="${inner}" fill="none" stroke="${darken(L, 0.45)}" stroke-opacity="0.32" stroke-width="5"/>
    <path d="${inner}" fill="none" stroke="#ffffff" stroke-opacity="0.35" stroke-width="1.5"/>
    <g clip-path="url(#${id}-body)">
        <!-- alas kaca tebal -->
        <rect x="${x}" y="${bottom - b.base}" width="${b.w}" height="${b.base}" fill="${lighten(L, 0.3)}" opacity="0.8"/>
        <rect x="${x}" y="${bottom - b.base}" width="${b.w}" height="2.5" fill="#fff" opacity="0.7"/>
        <rect x="${x}" y="${bottom - 10}" width="${b.w}" height="10" fill="${darken(L, 0.25)}" opacity="0.25"/>
        <!-- dinding kaca -->
        <path d="${body}" fill="url(#${id}-glass)"/>
        <!-- pantulan cahaya -->
        <rect x="${x + b.wall + 12}" y="${top + 26}" width="${b.w * 0.06}" height="${b.h - b.base - 40}" fill="url(#${id}-hl)" filter="url(#${id}-soft)" opacity="0.75"/>
        <rect x="${x + b.w - b.wall - 22}" y="${top + 40}" width="7" height="${b.h - b.base - 70}" fill="#fff" opacity="0.38" filter="url(#${id}-soft)"/>
        <rect x="${x + 30}" y="${top + 6}" width="${b.w - 60}" height="5" fill="#fff" opacity="0.5" filter="url(#${id}-soft)"/>
    </g>
    <path d="${body}" fill="none" stroke="#ffffff" stroke-opacity="0.85" stroke-width="2.5"/>
    <path d="${body}" fill="none" stroke="#000000" stroke-opacity="0.16" stroke-width="1.2"/>

    <!-- label -->
    ${label(p, b, cx, labelY, b.label.w, b.label.h)}

    <!-- tutup -->
    ${cap(id, b.cap.kind, capX, capY, b.cap.w, b.cap.h)}
    `;

    return { markup, top: capY, labelCenterY: labelY + b.label.h / 2, w: b.w };
}

function shadow(id, cx, bottom, w, liquid) {
    return `<defs>
        <filter id="${id}-sh1" x="-50%" y="-200%" width="200%" height="500%"><feGaussianBlur stdDeviation="9"/></filter>
        <filter id="${id}-sh2" x="-50%" y="-200%" width="200%" height="500%"><feGaussianBlur stdDeviation="30"/></filter>
        <filter id="${id}-sh3" x="-50%" y="-200%" width="200%" height="500%"><feGaussianBlur stdDeviation="16"/></filter>
    </defs>
    <ellipse cx="${cx + 30}" cy="${bottom + 26}" rx="${w * 0.95}" ry="38" fill="#000" opacity="0.10" filter="url(#${id}-sh2)"/>
    <ellipse cx="${cx}" cy="${bottom + 22}" rx="${w * 0.42}" ry="20" fill="${liquid}" opacity="0.42" filter="url(#${id}-sh3)"/>
    <ellipse cx="${cx}" cy="${bottom + 2}" rx="${w * 0.56}" ry="9" fill="#000" opacity="0.32" filter="url(#${id}-sh1)"/>`;
}

function backdrop(scent) {
    const [wallTop, wallBottom, floor] = backdrops[scent];
    return `<defs>
        <linearGradient id="bg-wall" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${lighten(wallTop, 0.25)}"/><stop offset="1" stop-color="${wallBottom}"/></linearGradient>
        <linearGradient id="bg-floor" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${darken(floor, 0.04)}"/><stop offset="1" stop-color="${lighten(floor, 0.35)}"/></linearGradient>
        <radialGradient id="bg-light" cx="0.42" cy="0.32" r="0.75"><stop offset="0" stop-color="#fff" stop-opacity="0.55"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>
        <radialGradient id="bg-vig" cx="0.5" cy="0.5" r="0.75"><stop offset="0.6" stop-color="#000" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity="0.10"/></radialGradient>
        <filter id="bg-blur" x="0" y="-50%" width="100%" height="200%"><feGaussianBlur stdDeviation="16"/></filter>
    </defs>
    <rect width="1200" height="1200" fill="url(#bg-wall)"/>
    <rect y="840" width="1200" height="360" fill="url(#bg-floor)"/>
    <rect y="818" width="1200" height="44" fill="${darken(wallBottom, 0.03)}" filter="url(#bg-blur)" opacity="0.8"/>
    <rect width="1200" height="1200" fill="url(#bg-light)"/>
    <rect width="1200" height="1200" fill="url(#bg-vig)"/>`;
}

// Pantulan lembut di lantai.
// x0/w/h: area kanvas (koordinat lokal) yang boleh berisi pantulan. Bawaan = kanvas foto produk 1200x1200.
function reflection(id, inner, bottom, x0 = 0, w = 1200, h = 1200) {
    return `<defs>
        <linearGradient id="${id}-rf" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity="0.22"/><stop offset="0.35" stop-color="#fff" stop-opacity="0"/></linearGradient>
        <mask id="${id}-rm" maskUnits="userSpaceOnUse" x="${x0}" y="${bottom}" width="${w}" height="${h - bottom}"><rect x="${x0}" y="${bottom}" width="${w}" height="${h - bottom}" fill="url(#${id}-rf)"/></mask>
    </defs>
    <g mask="url(#${id}-rm)"><g transform="translate(0 ${bottom * 2}) scale(1 -1)">${inner}</g></g>`;
}

function box(p, cx, bottom) {
    const b = brands[p.brand];
    const w = 300, h = 470, d = 56, x = cx - w / 2, y = bottom - h;
    const side = lighten(b.box, b.box === '#1b1917' ? 0.0 : 0) ;
    return `<defs>
        <linearGradient id="box-front" x1="0" x2="1"><stop offset="0" stop-color="${darken(b.box, 0.05)}"/><stop offset="0.5" stop-color="${lighten(b.box, 0.08)}"/><stop offset="1" stop-color="${darken(b.box, 0.04)}"/></linearGradient>
    </defs>
    <ellipse cx="${cx + 40}" cy="${bottom + 18}" rx="${w * 0.75}" ry="30" fill="#000" opacity="0.14" filter="url(#bx-sh)"/>
    <path d="M${x + w},${y} L${x + w + d},${y - d * 0.45} L${x + w + d},${bottom - d * 0.45} L${x + w},${bottom} Z" fill="${darken(side, 0.18)}"/>
    <path d="M${x},${y} L${x + d},${y - d * 0.45} L${x + w + d},${y - d * 0.45} L${x + w},${y} Z" fill="${lighten(b.box, 0.12)}"/>
    <rect x="${x}" y="${y}" width="${w}" height="${h}" fill="url(#box-front)"/>
    <rect x="${x + 18}" y="${y + 18}" width="${w - 36}" height="${h - 36}" fill="none" stroke="${b.boxInk}" stroke-opacity="0.35" stroke-width="1"/>
    <text x="${cx}" y="${y + 92}" text-anchor="middle" font-family="Inter" font-weight="600" font-size="13" letter-spacing="5" fill="${b.boxInk}">${esc(p.brand.toUpperCase())}</text>
    <text x="${cx}" y="${y + h * 0.5}" text-anchor="middle" font-family="Cormorant Garamond" font-weight="600" font-size="${p.name.length > 11 ? 40 : 48}" fill="${b.boxInk}">${esc(p.name)}</text>
    <rect x="${cx - 22}" y="${y + h * 0.5 + 26}" width="44" height="1.2" fill="${b.boxInk}" opacity="0.7"/>
    <text x="${cx}" y="${y + h - 60}" text-anchor="middle" font-family="Inter" font-weight="500" font-size="11" letter-spacing="3" fill="${b.boxInk}" opacity="0.85">${CONC[p.conc]} · ${p.ml} ML</text>`;
}

function svg(content, width = 1200, height = 1200) {
    return `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
    <defs><filter id="bx-sh" x="-50%" y="-200%" width="200%" height="500%"><feGaussianBlur stdDeviation="22"/></filter></defs>
    ${content}</svg>`;
}

// Font dan renderer dimuat dari folder alat di luar project (lihat petunjuk di render-products.mjs).
export function createRenderer(toolsDir) {
    const require = createRequire(path.join(toolsDir, 'package.json'));
    const { Resvg } = require('@resvg/resvg-js');
    const sharp = require('sharp');
    const fontDir = (pkg) => path.join(toolsDir, 'node_modules', '@expo-google-fonts', pkg);
    const fontFiles = [
        path.join(fontDir('cormorant-garamond'), 'CormorantGaramond_500Medium.ttf'),
        path.join(fontDir('cormorant-garamond'), 'CormorantGaramond_600SemiBold.ttf'),
        path.join(fontDir('inter'), 'Inter_400Regular.ttf'),
        path.join(fontDir('inter'), 'Inter_500Medium.ttf'),
        path.join(fontDir('inter'), 'Inter_600SemiBold.ttf'),
    ];

    return async function render(svgMarkup, width, file, quality = 84) {
        const png = new Resvg(svgMarkup, {
            fitTo: { mode: 'width', value: width },
            font: { fontFiles, loadSystemFonts: false, defaultFontFamily: 'Inter' },
        }).render().asPng();
        fs.mkdirSync(path.dirname(file), { recursive: true });
        await sharp(png).webp({ quality, effort: 5 }).toFile(file);
        console.log(file, Math.round(fs.statSync(file).size / 1024) + 'KB');
    };
}

export { CONC, products, backdrops, brands, hex, toHex, mix, lighten, darken, esc, roundedPath, bottle, shadow, backdrop, reflection, box, svg };
