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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Brand blue, anchored at #0084FF (the 600 shade used throughout the UI).
                indigo: {
                    50: '#EAF4FF',
                    100: '#D5E9FF',
                    200: '#ABD3FF',
                    300: '#7DBBFF',
                    400: '#4CA2FF',
                    500: '#1F8DFF',
                    600: '#0084FF',
                    700: '#006AD1',
                    800: '#0051A3',
                    900: '#003A75',
                    950: '#00234A',
                },
            },
        },
    },

    plugins: [forms],
};
