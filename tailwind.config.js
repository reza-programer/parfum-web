import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Design system marketplace (docs/plans/redesign-marketplace.md).
 * Nama token (paper, ink, line, primary) dipertahankan supaya semua view ikut berubah serentak.
 * Sudut membulat memakai skala bawaan Tailwind: rounded-md 6px (tombol, input), rounded-lg 8px (kartu).
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
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                // Hanya untuk wordmark logo.
                serif: ['"Cormorant Garamond"', ...defaultTheme.fontFamily.serif],
            },
            fontSize: {
                caption: ['11px', { lineHeight: '16px' }],
                label: ['12px', { lineHeight: '18px' }],
                body: ['14px', { lineHeight: '20px' }],
                product: ['14px', { lineHeight: '20px' }],
                section: ['clamp(1.0625rem, 1rem + 0.3vw, 1.25rem)', { lineHeight: '1.35' }],
                page: ['clamp(1.25rem, 1.15rem + 0.45vw, 1.5rem)', { lineHeight: '1.3' }],
                banner: ['clamp(1.125rem, 0.75rem + 1.9vw, 2.5rem)', { lineHeight: '1.15' }],
            },
            colors: {
                paper: {
                    DEFAULT: '#f5f5f5', // latar halaman
                    card: '#ffffff', // kartu
                    raised: '#f7f7f7', // latar foto, skeleton, hover baris
                },
                ink: {
                    DEFAULT: '#212121', // teks utama
                    2: '#6d6d6d', // teks sekunder (kontras AA di putih dan #f5f5f5)
                    3: '#6d6d6d',
                    faint: '#bdbdbd', // disabled, placeholder
                },
                primary: {
                    DEFAULT: '#e11d48', // merah mawar: tombol utama, harga, tab aktif
                    dark: '#be123c',
                    soft: '#fff1f2',
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
                line: '#eaeaea',
            },
            ringColor: {
                line: '#eaeaea',
            },
            letterSpacing: {
                label: '0.04em',
                nav: '0.08em',
            },
            transitionTimingFunction: {
                out: 'cubic-bezier(0.22, 1, 0.36, 1)',
            },
            boxShadow: {
                card: '0 1px 2px rgba(0, 0, 0, 0.04)',
                hover: '0 6px 18px rgba(0, 0, 0, 0.08)',
                float: '0 10px 30px rgba(0, 0, 0, 0.12)',
            },
            maxWidth: {
                page: '1200px',
            },
        },
    },

    plugins: [forms],
};
