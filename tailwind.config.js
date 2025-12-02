import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
		'./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
		 './vendor/laravel/jetstream/**/*.blade.php',
		 './storage/framework/views/*.php',
		 './resources/views/**/*.blade.php',
		 "./vendor/robsontenorio/mary/src/View/Components/**/*.php"
	],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
			colors: {
                logigate: {
                    primary:   '#0057D9',
                    secondary: '#008CFF',
                    tertiary:  '#15C9E8',
                    dark:      '#002F6C',
                    graylight: '#D3D3D3',
                    graymid:   '#8A8A8A',
                    graydark:  '#3A3A3A',
                    accent:    '#B38CFF',
                }
            },
            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
            },
            keyframes: {
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
            },
            animation: {
                'fade-in': 'fade-in 0.5s ease-out',
            }
        },
    },

    plugins: [
		forms,
		typography,
		require("daisyui")
	],
};
