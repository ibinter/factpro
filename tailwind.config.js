import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Identité IBIG FactPro
                brand: {
                    50: '#eef6ff',
                    100: '#d9eaff',
                    200: '#bcdbff',
                    300: '#8ec4ff',
                    400: '#59a3ff',
                    500: '#337fff',
                    600: '#0062CC', // bleu principal logo
                    700: '#004fa6',
                    800: '#003d80',
                    900: '#002D5B', // marine logo
                    950: '#001d3d',
                },
                gold: {
                    300: '#f7d97c',
                    400: '#F0C040', // or logo
                    500: '#e0a92a',
                    600: '#C88000',
                },
            },
        },
    },

    plugins: [forms],
};
