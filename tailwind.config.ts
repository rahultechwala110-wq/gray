import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./components/**/*.{js,ts,jsx,tsx,mdx}",
    "./app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        primary: "#1A1A1A",
        "background-light": "#F9F7F2",
        "background-dark": "#121212",
        "accent-gold": "#C5A059",
        "gray-muted": "#8E8E8E",
      },
      fontFamily: {
      display: ["var(--font-qlassy)", "serif"],
      sans: ["var(--font-inter)", "sans-serif"],
      qlassy: ["var(--font-qlassy)", "serif"],
      glacial: ["var(--font-glacial)", "sans-serif"],
    },
      borderRadius: {
        DEFAULT: "4px",
      },
      // --- YAHAN SE ADD KIYA HAI ---
      animation: {
        marquee: 'marquee 15s linear infinite',
      },
      keyframes: {
        marquee: {
          '0%': { transform: 'translateX(0%)' },
          '100%': { transform: 'translateX(-50%)' },
        },
      },
      // ----------------------------
    },
  },
  plugins: [],
};

export default config;