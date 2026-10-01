/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                primary: '#0ea5e9', // Sky 500
                'primary-dark': '#0284c7', // Sky 600
                'primary-light': '#38bdf8', // Sky 400
                secondary: '#0f172a', // Slate 900
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
