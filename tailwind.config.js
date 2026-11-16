import defaultTheme from 'tailwindcss/defaultTheme';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                page: '#EEF1FF',
                surface: '#FFFFFF',
                sidebar: '#F7F8FF',
                line: '#E3E6F6',
                ink: '#161B33',
                muted: '#6B7085',
                primary: {
                    DEFAULT: '#6D5BE8',
                    soft: '#EDEBFF',
                },
                promo: '#C8E6FB',
                success: {
                    DEFAULT: '#22C55E',
                    soft: '#DCFCE7',
                },
                error: {
                    DEFAULT: '#EF4444',
                    soft: '#FEE2E2',
                },
                warning: {
                    DEFAULT: '#F59E0B',
                    soft: '#FEF3C7',
                },
            },
            borderRadius: {
                card: '1.25rem',
            },
            boxShadow: {
                card: '0 1px 2px rgba(22, 27, 51, 0.04)',
                primary: '0 8px 20px rgba(109, 91, 232, 0.30)',
            },
        },
    },
    plugins: [daisyui],
    daisyui: {
        themes: [
            {
                edudash: {
                    primary: '#6D5BE8',
                    'primary-content': '#FFFFFF',
                    secondary: '#EDEBFF',
                    'secondary-content': '#3D3A6B',
                    accent: '#C8E6FB',
                    'accent-content': '#161B33',
                    neutral: '#161B33',
                    'neutral-content': '#FFFFFF',
                    'base-100': '#FFFFFF',
                    'base-200': '#EEF1FF',
                    'base-300': '#E3E6F6',
                    'base-content': '#161B33',
                    info: '#38BDF8',
                    success: '#22C55E',
                    warning: '#F59E0B',
                    error: '#EF4444',
                    '--rounded-box': '1.25rem',
                    '--rounded-btn': '9999px',
                    '--rounded-badge': '9999px',
                },
            },
        ],
    },
};
