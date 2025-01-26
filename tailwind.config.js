/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    './vendor/masmerise/livewire-toaster/resources/views/*.blade.php', // 👈
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

