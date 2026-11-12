// Generator render produk (gaya foto studio) untuk katalog demo Toko Parfum.
// Hasilnya render digital, bukan foto kamera: produk dan brand di CatalogDemoSeeder fiktif.
//
// Pemakaian (di luar project, supaya dependensinya tidak masuk package.json):
//   mkdir render-tools && cd render-tools && npm init -y
//   npm install @resvg/resvg-js@2.6.2 sharp@0.33.5 @expo-google-fonts/cormorant-garamond@0.2.3 @expo-google-fonts/inter@0.2.3
//   node <project>/database/seeders/images/source/render-products.mjs <render-tools> <project>/database/seeders/images/products [slug]
// Satu produk = 3 gambar 1200x1200 WebP: tampak depan, dengan kemasan, detail label.
import path from 'node:path';
import { products, bottle, shadow, backdrop, reflection, box, svg, createRenderer } from './render-lib.mjs';

const [toolsDir, outDir, only] = process.argv.slice(2);
const render = createRenderer(toolsDir);

function scenes(p) {
    const bottom = 905;

    // 1. Tampak depan
    const front = bottle(p, 'f', 600, bottom);
    const s1 = svg(`${backdrop(p.scent)}<g transform="translate(600 ${bottom}) scale(1.2) translate(-600 ${-bottom})">${reflection('f', front.markup, bottom)}${shadow('f', 600, bottom, front.w, p.liquid)}${front.markup}</g>`);

    // 2. Dengan kotak kemasan
    const boxed = bottle(p, 'b', 775, bottom);
    const s2 = svg(`${backdrop(p.scent)}<g transform="translate(600 ${bottom}) scale(1.1) translate(-600 ${-bottom})">${box(p, 385, bottom - 40)}${reflection('b', boxed.markup, bottom)}${shadow('b', 775, bottom, boxed.w, p.liquid)}${boxed.markup}</g>`);

    // 3. Detail label (makro)
    const detail = bottle(p, 'd', 600, bottom);
    const k = 2.15;
    const cy = detail.labelCenterY - 40;
    const s3 = svg(`${backdrop(p.scent)}<g transform="translate(600 640) scale(${k}) translate(-600 ${-cy})">${shadow('d', 600, bottom, detail.w, p.liquid)}${detail.markup}</g>`);

    return [s1, s2, s3];
}


const list = only ? products.filter((p) => p.slug === only) : products;
for (const p of list) {
    const shots = scenes(p);
    for (let i = 0; i < shots.length; i++) {
        await render(shots[i], 1200, path.join(outDir, `${p.slug}-${i + 1}.webp`));
    }
}
