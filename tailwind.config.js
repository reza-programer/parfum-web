import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Design system monokrom (docs/plans/redesign-monokrom.md).
 * Nama token lama (paper, ink, line) dipertahankan supaya semua view ikut berubah serentak.
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
        // Sudut tegas di semua komponen; hanya lingkaran (titik, angka keranjang) yang tetap bulat.
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
                serif: ['"Cormorant Garamond"', ...defaultTheme.fontFamily.serif],
            },
            fontSize: {
                caption: ['11px', { lineHeight: '16px' }],
                label: ['12px', { lineHeight: '18px' }],
                body: ['14px', { lineHeight: '22px' }],
                product: ['15px', { lineHeight: '22px' }],
                section: ['clamp(1.375rem, 1.25rem + 0.4vw, 1.5rem)', { lineHeight: '1.2' }],
                page: ['clamp(1.875rem, 1.55rem + 1.1vw, 2.5rem)', { lineHeight: '1.1' }],
                hero: ['clamp(2.25rem, 1.65rem + 2vw, 3.25rem)', { lineHeight: '1.05' }],
            },
            colors: {
                paper: {
                    DEFAULT: '#f8f8f6', // latar halaman
                    card: '#ffffff', // surface
                    raised: '#f1f1ef', // latar foto/ilustrasi, skeleton
                },
                ink: {
                    DEFAULT: '#171717', // teks utama
                    2: '#707070', // teks sekunder (#777777 di brief, digelapkan sedikit untuk kontras AA)
                    3: '#707070', // teks tersier = sekunder, supaya teks kecil tetap terbaca
                    faint: '#bdbdbd', // disabled, placeholder
                },
                primary: {
                    DEFAULT: '#111111',
                    dark: '#000000',
                    soft: '#f1f1ef',
                },
                danger: '#b42318',
            },
            borderColor: {
                line: '#e8e8e8',
            },
            ringColor: {
                line: '#e8e8e8',
            },
            letterSpacing: {
                label: '0.08em',
                nav: '0.12em',
            },
            transitionTimingFunction: {
                out: 'cubic-bezier(0.22, 1, 0.36, 1)',
            },
            boxShadow: {
                // Tanpa bayangan: pemisah memakai garis tipis.
                float: 'none',
            },
            maxWidth: {
                page: '1280px',
            },
        },
    },

    plugins: [forms],
};
