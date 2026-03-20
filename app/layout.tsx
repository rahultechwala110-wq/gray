import type { Metadata } from "next";
import localFont from "next/font/local";
import "./globals.css";
import ClientWrapper from "./components/ClientWrapper";
import NextTopLoader from 'nextjs-toploader';

// ✅ Qlassy font configuration
const qlassy = localFont({
  src: [
    { path: './fonts/Qlassy-Regular.otf', weight: '400', style: 'normal' },
    { path: './fonts/Qlassy-Italic.otf', weight: '400', style: 'italic' },
    { path: './fonts/Qlassy-Semibold.otf', weight: '600', style: 'normal' },
    { path: './fonts/Qlassy-Semibold-Italic.otf', weight: '600', style: 'italic' },
    { path: './fonts/Qlassy-Bold.ttf', weight: '700', style: 'normal' },
    { path: './fonts/Qlassy-Bold-Italic.otf', weight: '700', style: 'italic' },
  ],
  variable: '--font-qlassy',
});

// ✅ Glacial Indifference font configuration
const glacialIndifference = localFont({
  src: [
    { path: './fonts/glacial-indifference.regular.otf', weight: '400', style: 'normal' },
    { path: './fonts/glacial-indifference.bold.otf', weight: '700', style: 'normal' },
  ],
  variable: '--font-glacial',
});

export const metadata: Metadata = {
  title: "GRAY",
  description: "Crafted by nature, refined by time.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" className="scroll-smooth">
      <head>
        <link 
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" 
          rel="stylesheet" 
        />
      </head>
      {/* ✅ Body mein fonts, loader aur client wrapper ek sath */}
      <body className={`${qlassy.variable} ${glacialIndifference.variable}`}>
        {/* Scent-inspired Elegant Loader */}
        <NextTopLoader 
          color="var(--loader-color, #1a1a1a)" 
          initialPosition={0.08}
          crawlSpeed={200}
          height={2}
          crawl={true}
          showSpinner={false} 
          easing="ease"
          speed={700}
          shadow="0 0 10px #1a1a1a,0 0 5px #1a1a1a"
        />

        <ClientWrapper>
          {children}
        </ClientWrapper>
      </body>
    </html>
  );
}