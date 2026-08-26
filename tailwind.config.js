/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                pink: {
                    50: '#FFF4F8',
                    100: '#FFE1EC',
                    200: '#FFC2D8',
                    500: '#F20162',
                    600: '#DE0159',
                    700: '#C90154',
                    800: '#A80B4C',
                },
                slate: {
                    300: '#A8B1B7',
                    500: '#5A656D',
                    700: '#333B41',
                    900: '#141719',
                },
                teal: {
                    50: '#F0FAF7',
                    100: '#DCF2ED',
                    500: '#12A085',
                    700: '#0B6E5C',
                },
                gold: {
                    soft: '#FDF4E3',
                    line: '#F0DFBC',
                    DEFAULT: '#C98A1E',
                },
                mist: '#F7F7F5',
                line: '#E8E8E4',
                ink: '#141719',
            },
            fontFamily: {
                serif: ['Fraunces', 'Georgia', 'serif'],
                sans: ['Inter', 'Hind Siliguri', 'sans-serif'],
                bn: ['Hind Siliguri', 'Inter', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
