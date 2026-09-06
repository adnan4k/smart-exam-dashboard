/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    './vendor/masmerise/livewire-toaster/resources/views/*.blade.php', // 👈
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f5f7f6',
          100: '#e6ebe9',
          200: '#cdd7d4',
          300: '#a8bcb6',
          400: '#7a9a92',
          500: '#58706D',
          600: '#455956',
          700: '#394846',
          800: '#303c3a',
          900: '#293331',
          950: '#161d1c',
        },
        sage: {
          50: '#f6f7f4',
          100: '#eaede6',
          500: '#7C8A6E',
          600: '#67745b',
        },
        khaki: {
          100: '#f5f5ee',
          500: '#B0B087',
          600: '#94946b',
        },
        ink: {
          500: '#4B5757',
          700: '#384343',
          800: '#2d3535',
          900: '#1f2525',
        },
        cream: {
          50: '#fdfdfb',
          100: '#f8f8f3',
          200: '#efefe5',
          300: '#E3E3D1',
        },
      },
    },
  },
  plugins: [],
}

