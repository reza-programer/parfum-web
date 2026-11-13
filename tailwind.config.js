import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Design system v3: gaya newbalance.co.id (docs/plans/redesign-nb.md).
 * Latar putih polos, teks hitam, aksen merah, sudut tegas, judul kapital tebal.
 * Nama token (paper, ink, line, primary) dipertahankan supaya semua view ikut berubah serentak.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        // Sudut tegas di semua komponen; hanya lingkaran (titik carousel, angka keranjang) yang bulat.
        borderRadius: {
            none: '0',
            sm: '0',
            DEFAULT: '0',
            md: '0',
            lg: '0',
            xl: '0',
            '2xl': '0',
            '3xl': '0',
            full: '9999px',
        },
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                // Hanya untuk wordmark logo.
                serif: ['"Cormorant Garamond"', ...defaultTheme.fontFamily.serif],
            },
            fontSize: {
                caption: ['11px', { lineHeight: '16px' }],
                label: ['12px', { lineHeight: '18px' }],
                body: ['14px', { lineHeight: '22px' }],
                product: ['14px', { lineHeight: '20px' }],
                section: ['clamp(1.25rem, 1.1rem + 0.6vw, 1.625rem)', { lineHeight: '1.2' }],
                page: ['clamp(1.375rem, 1.2rem + 0.8vw, 1.875rem)', { lineHeight: '1.2' }],
                banner: ['clamp(1.875rem, 1.1rem + 2.8vw, 3.5rem)', { lineHeight: '1.05' }],
            },
            colors: {
                paper: {
                    DEFAULT: '#ffffff', // latar halaman: putih polos
                    card: '#ffffff',
                    raised: '#f5f5f5', // bilah utilitas, latar foto, kotak ringkasan
                },
                ink: {
                    DEFAULT: '#111111', // teks utama
                    2: '#6b6b6b', // teks sekunder (kontras AA di putih)
                    3: '#6b6b6b',
                    faint: '#bdbdbd', // disabled, placeholder
                },
                night: '#1a1a1a', // footer gelap
                primary: {
                    DEFAULT: '#dc2626', // merah: tombol beli, titik aktif, brand di detail
                    dark: '#b91c1c',
                    soft: '#fef2f2',
                },
                success: {
                    DEFAULT: '#047857',
                    soft: '#ecfdf5',
                },
                warning: {
                    DEFAULT: '#b45309',
                    soft: '#fff7ed',
                },
                danger: '#b42318',
            },
            borderColor: {
                line: '#e5e5e5',
            },
            ringColor: {
                line: '#e5e5e5',
            },
            letterSpacing: {
                heading: '0.02em',
                label: '0.04em',
                nav: '0.06em',
            },
            transitionTimingFunction: {
                out: 'cubic-bezier(0.22, 1, 0.36, 1)',
            },
            boxShadow: {
                // Tanpa bayangan di konten; hanya lapisan melayang (dropdown, mega menu) yang punya bayangan tipis.
                card: 'none',
                hover: 'none',
                float: '0 12px 28px rgba(0, 0, 0, 0.08)',
            },
            maxWidth: {
                page: '1280px',
            },
        },
    },

    plugins: [forms],
};
