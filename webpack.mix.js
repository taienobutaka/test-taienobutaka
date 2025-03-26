const mix = require("laravel-mix");

mix
  .js("resources/js/app.js", "public/js") // Vue.jsのエントリーポイント
  .vue() // Vue.jsを有効化
  .postCss("resources/css/app.css", "public/css", [require("tailwindcss")]); // Tailwind CSSを使用
