import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import flowbite from 'flowbite/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', 
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./node_modules/flowbite/**/*.js",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
        colors: {
        heading: '#111827', 
        body: '#4b5563',
        brand: '#1c64f2', // Flowbite default blue
        default: {
          DEFAULT: '#e5e7eb',
          medium: '#d1d5db',
        },
        neutral: {
          'tertiary': '#f3f4f6',
          'secondary-medium': '#f9fafb',
        }
      },
      borderRadius: {
        'base': '0.5rem', // Flowbite base rounded corner
      }
    },

    plugins: [
        forms,
        require('flowbite/plugin'),
    ],
};
