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
                page: 'var(--color-bg-page, #F5F7FA)',
                surface: 'var(--color-bg-surface, #FFFFFF)',
                sidebar: 'var(--color-bg-sidebar, #F8FAFC)',
                raised: 'var(--color-bg-raised, #ECEFF4)',
                line: 'var(--color-line, #E2E8F0)',
                ink: 'var(--color-ink, #111827)',
                secondary: 'var(--color-secondary, #4B5565)',
                muted: 'var(--color-muted, #6B7487)',
                primary: {
                    DEFAULT: '#4F46E5',
                    soft: 'var(--color-primary-soft, #EEF2FF)',
                },
                promo: 'var(--color-bg-sidebar, #F8FAFC)',
                success: {
                    DEFAULT: '#16A34A',
                    soft: '#DCFCE7',
                },
                error: {
                    DEFAULT: '#DC2626',
                    soft: '#FEE2E2',
                },
                warning: {
                    DEFAULT: '#D97706',
                    soft: '#FEF3C7',
                },
            },
            borderRadius: {
                card: '1rem',
            },
            boxShadow: {
                card: 'var(--color-card-shadow, 0 1px 2px rgba(17, 24, 39, 0.05))',
                primary: '0 1px 2px rgba(17, 24, 39, 0.08)',
            },
            transitionTimingFunction: {
                unslop: 'cubic-bezier(0.22, 1, 0.36, 1)',
            },
        },
    },
    plugins: [daisyui],
    daisyui: {
        themes: [
            {
                edudash: {
                    primary: '#4F46E5',
                    'primary-content': '#FFFFFF',
                    secondary: '#EEF2FF',
                    'secondary-content': '#1E1B4B',
                    accent: '#ECEFF4',
                    'accent-content': '#111827',
                    neutral: '#111827',
                    'neutral-content': '#FFFFFF',
                    'base-100': '#FFFFFF',
                    'base-200': '#F5F7FA',
                    'base-300': '#E2E8F0',
                    'base-content': '#111827',
                    info: '#0284C7',
                    success: '#16A34A',
                    warning: '#D97706',
                    error: '#DC2626',
                    '--rounded-box': '1rem',
                    '--rounded-btn': '0.5rem',
                    '--rounded-badge': '0.375rem',
                },
                'edudash-dark': {
                    primary: '#6366F1',
                    'primary-content': '#FFFFFF',
                    secondary: '#1E1B4B',
                    'secondary-content': '#EEF2FF',
                    accent: '#334155',
                    'accent-content': '#F8FAFC',
                    neutral: '#F8FAFC',
                    'neutral-content': '#0F172A',
                    'base-100': '#1E293B',
                    'base-200': '#0F172A',
                    'base-300': '#334155',
                    'base-content': '#F8FAFC',
                    info: '#38BDF8',
                    success: '#22C55E',
                    warning: '#F59E0B',
                    error: '#EF4444',
                    '--rounded-box': '1rem',
                    '--rounded-btn': '0.5rem',
                    '--rounded-badge': '0.375rem',
                },
            },
        ],
    },
};
