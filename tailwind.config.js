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
            },
            colors: {
                cream: {
                    DEFAULT: '#FBF6EF',
                    50: '#FFFDFA',
                    100: '#FBF6EF',
                    200: '#F3E9D9',
                },
                ink: {
                    DEFAULT: '#2B211A',
                    900: '#201812',
                    700: '#2B211A',
                    500: '#5c4d40',
                    400: '#7a6a5c',
                    300: '#a99787',
                    200: '#E0DED8',
                    100: 'rgba(43,33,26,0.1)',
                },
                terracotta: {
                    DEFAULT: '#B5502A',
                    50: '#FBEEE7',
                    600: '#B5502A',
                    700: '#8f3f20',
                },
                forest: {
                    DEFAULT: '#2F5233',
                    600: '#2F5233',
                    700: '#223d26',
                },
                ochre: {
                    DEFAULT: '#D4A73B',
                    500: '#D4A73B',
                    600: '#b78c28',
                },
                clay: {
                    DEFAULT: '#C97B4A',
                    500: '#C97B4A',
                    600: '#a8633a',
                },
            },
        },
    },

    plugins: [forms],
};
