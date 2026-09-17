import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Work Sans"', ...defaultTheme.fontFamily.sans],
                serif: ['Newsreader', ...defaultTheme.fontFamily.serif],
                // Wordmark only (nav/footer logo) — matches the "Azohub" lockup from Claude Design.
                brand: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // "Confiance Digitale" — bleu/blanc. Les noms de tokens restent ceux
                // du thème "Terre & Artisanat" (cream/ink/terracotta/forest/ochre/clay)
                // pour ne pas avoir à retoucher les ~45 vues qui les utilisent : seules
                // les valeurs changent.
                cream: {
                    DEFAULT: '#F7FAFD',
                    50: '#FFFFFF',
                    100: '#F7FAFD',
                    200: '#EAF1F8',
                },
                ink: {
                    DEFAULT: '#0F2A5C',
                    900: '#0A1F42',
                    700: '#0F2A5C',
                    500: '#48597A',
                    400: '#6B7A93',
                    300: '#94A3B8',
                    200: '#DDE6F0',
                    100: 'rgba(15,42,92,0.08)',
                },
                terracotta: {
                    DEFAULT: '#2563EB',
                    50: '#E8F1FD',
                    600: '#2563EB',
                    700: '#1D4ED8',
                },
                forest: {
                    DEFAULT: '#16A34A',
                    600: '#16A34A',
                    700: '#15803D',
                },
                ochre: {
                    DEFAULT: '#EAB308',
                    500: '#EAB308',
                    600: '#CA8A04',
                },
                clay: {
                    DEFAULT: '#38BDF8',
                    500: '#38BDF8',
                    600: '#0EA5E9',
                },
            },
        },
    },

    plugins: [forms],
};
