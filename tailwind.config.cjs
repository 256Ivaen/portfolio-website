/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
      "./app/**/*.{js,ts,jsx,tsx,mdx}",
      "./components/**/*.{js,ts,jsx,tsx,mdx}",
    ],
    theme: {
      extend: {
        colors: {
          primary: '#33632c',   
          secondary: '#12324f', 
          cardBg: '#002776',
          background: '#002776',
          'text-primary': '#ffffff',
          'text-secondary': '#d1d5db',
        },
      },
    },
    plugins: [],
  }