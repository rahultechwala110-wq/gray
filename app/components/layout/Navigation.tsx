'use client';

import { useState, useEffect, useRef } from 'react';
import Image from 'next/image';
import Icon from '../ui/Icon';
import { Facebook, Instagram, Twitter, Linkedin, MessageCircle, ChevronDown } from "lucide-react";
import Link from 'next/link';
import PerfumePopup from '../sections/PerfumeFinder'; 

const megaMenuData = {
  extraitDeParfum: {
    label: 'Extrait de Fragrance',
    columns: [
      {
        heading: 'Man',
        items: [
          { name: 'Brave',    image: '/mega-menu/01.jpg', href: '/brave-mens-perfume-55ml' },
          { name: 'Boss',     image: '/mega-menu/02.jpg', href: '/boss-mens-perfume-55m' },
          { name: 'Gentle',   image: '/mega-menu/03.jpg', href: '/gentle-mens-perfume-55ml' },
          { name: 'Bold',     image: '/mega-menu/04.jpg', href: '/gold-men-perfume-55ml' },
          { name: 'Generous', image: '/mega-menu/05.jpg', href: '/generous-mens-perfume-55ml' },
          { name: 'Groomed',  image: '/mega-menu/06.jpg', href: '/groomed-mens-perfume-55m' },
        ],
      },
      {
        heading: 'Woman',
        items: [
          { name: 'Bliss',      image: '/mega-menu/07.jpg', href: '/bliss-womens-perfume-55ml' },
          { name: 'Gorgeous',   image: '/mega-menu/08.jpg', href: '/gorgeous-womens-perfume-55ml' },
          { name: 'Braveheart', image: '/mega-menu/09.jpg', href: '/braveheart-womens-perfume-55ml' },
          { name: 'Glorious',   image: '/mega-menu/10.jpg', href: '/glorious-womens-perfume-55ml' },
          { name: 'Brilliance', image: '/mega-menu/11.jpg', href: '/brilliance-womens-perfume-55ml' },
          { name: 'Gifted',     image: '/mega-menu/12.jpg', href: '/gifted-womens-perfume-55ml' },
        ],
      },
      {
        heading: 'Discovery',
        items: [
          { name: 'Men',   image: '/mega-menu/18.png', href: '/all-products?category=Man' },
          { name: 'Women', image: '/mega-menu/19.jpg', href: '/all-products?category=Woman' },
        ],
      },
    ],
  },
  aromaPod: {
    label: 'Aroma Pod',
    columns: [
      {
        heading: 'Unisex',
        items: [
          { name: 'B612',     image: '/mega-menu/13.jpg', href: '/aroma-pod/b612' },
          { name: 'Bulge',    image: '/mega-menu/14.jpg', href: '/aroma-pod/bulge' },
          { name: 'Brahe',    image: '/mega-menu/15.jpg', href: '/aroma-pod/brahe' },
          { name: 'Glese',    image: '/mega-menu/16.jpg', href: '/aroma-pod/glese' },
          { name: 'Ganymede', image: '/mega-menu/17.jpg', href: '/aroma-pod/ganymede' },
          { name: 'Gaspra',   image: '/mega-menu/20.jpg', href: '/aroma-pod/gaspra' },
        ],
      },
    ],
  },
};

const DEFAULT_IMAGE = '/mega-menu/19.jpg';
const BG = 'bg-[#0a0a0a]/75';

export default function Navigation() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [mobileNavOpen, setMobileNavOpen] = useState(false);
  const [megaOpen, setMegaOpen] = useState(false);
  const [mobilePerfumOpen, setMobilePerfumOpen] = useState(false);
  const [previewImage, setPreviewImage] = useState(DEFAULT_IMAGE);
  const [isPerfumeFinderOpen, setIsPerfumeFinderOpen] = useState(false);
  const [cartCount, setCartCount] = useState(0);

  const navRef = useRef<HTMLElement>(null);
  const closeTimer = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

  useEffect(() => {
    const updateCartCount = () => {
      const cartData = localStorage.getItem('cart');
      if (cartData) {
        try {
          const items = JSON.parse(cartData);
          if (Array.isArray(items)) {
            const total = items.reduce((acc, item) => acc + (Number(item.qty) || 1), 0);
            setCartCount(total);
          } else setCartCount(0);
        } catch { setCartCount(0); }
      } else setCartCount(0);
    };
    updateCartCount();
    window.addEventListener('storage', updateCartCount);
    window.addEventListener('cartUpdated', updateCartCount);
    return () => {
      window.removeEventListener('storage', updateCartCount);
      window.removeEventListener('cartUpdated', updateCartCount);
    };
  }, []);

  useEffect(() => {
    const onResize = () => { if (window.innerWidth >= 1024) setMobileNavOpen(false); };
    window.addEventListener('resize', onResize);
    return () => window.removeEventListener('resize', onResize);
  }, []);

  const openMega = () => { clearTimeout(closeTimer.current); setMegaOpen(true); setPreviewImage(DEFAULT_IMAGE); };
  const closeMega = () => {
    closeTimer.current = setTimeout(() => { setMegaOpen(false); setPreviewImage(DEFAULT_IMAGE); }, 120);
  };

  return (
    <>
      <PerfumePopup isOpen={isPerfumeFinderOpen} onClose={() => setIsPerfumeFinderOpen(false)} />

      {/* ── Single fixed wrapper — one blur layer for navbar + megamenu ── */}
      <div
        className={`fixed top-0 left-0 right-0 z-50 backdrop-blur-xl ${BG} transition-all duration-500`}
      >
        {/* NAVBAR */}
        <nav ref={navRef} className="w-full py-4">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between lg:grid lg:grid-cols-3">

            <div className="flex justify-start">
              <Link href="/" className="relative block w-24 sm:w-28 md:w-32 h-7 sm:h-8 md:h-10">
                <Image src="/logo-white.png" alt="Logo" fill className="object-contain object-left" priority />
              </Link>
            </div>

            <div className="hidden lg:flex justify-center items-center gap-5 xl:gap-7">
              <Link href="/about" className="relative text-white text-[13px] xl:text-[14px] tracking-[0.15em] font-glacial group whitespace-nowrap">
                About
                <span className="absolute -bottom-1 left-0 w-0 h-[1px] bg-white transition-all duration-300 group-hover:w-full" />
              </Link>

              <div className="relative" onMouseEnter={openMega} onMouseLeave={closeMega}>
                <button className={`relative text-white text-[13px] xl:text-[14px] tracking-[0.15em] font-glacial flex items-center gap-1 whitespace-nowrap ${megaOpen ? 'opacity-100' : ''}`}>
                  Fragrance
                  <span className={`absolute -bottom-1 left-0 h-[1px] bg-white transition-all duration-300 ${megaOpen ? 'w-full' : 'w-0'}`} />
                </button>
              </div>

              <button onClick={() => setIsPerfumeFinderOpen(true)} className="relative text-white text-[14px] xl:text-[15px] tracking-[0.15em] font-glacial group whitespace-nowrap">
                Discover Your Signature Fragrance
                <span className="absolute -bottom-1 left-0 w-0 h-[1px] bg-white transition-all duration-300 group-hover:w-full" />
              </button>

              <Link href="/contact" className="relative text-white text-[14px] xl:text-[15px] tracking-[0.15em] font-glacial group whitespace-nowrap">
                Contact Us
                <span className="absolute -bottom-1 left-0 w-0 h-[1px] bg-white transition-all duration-300 group-hover:w-full" />
              </Link>
            </div>

            <div className="flex justify-end items-center gap-3 sm:gap-4 text-white">
              <Link href="/login" aria-label="Login" className="hover:opacity-60 transition-opacity">
                <Icon name="person_outline" className="text-[20px] sm:text-[22px] md:text-[26px]" />
              </Link>
              <Link href="/cart" aria-label="Cart" className="hover:opacity-60 transition-opacity relative">
                <Icon name="shopping_cart" className="text-[20px] sm:text-[22px] md:text-[26px]" />
                {cartCount > 0 && (
                  <span className="absolute -top-1 -right-1 text-[9px] w-4 h-4 rounded-full flex items-center justify-center font-bold text-black font-glacial" style={{ backgroundColor: '#ffdda5' }}>
                    {cartCount}
                  </span>
                )}
              </Link>
              <button onClick={() => setIsMenuOpen(true)} aria-label="Open Sidebar" className="hidden lg:block hover:opacity-70 transition-all active:scale-90">
                <Icon name="grid_view" className="text-[24px] md:text-[28px]" />
              </button>
              <button onClick={() => setMobileNavOpen(true)} aria-label="Open Mobile Menu" className="lg:hidden hover:opacity-70 transition-all active:scale-90">
                <Icon name="menu" className="text-[26px]" />
              </button>
            </div>
          </div>
        </nav>

        {/* MEGA MENU — inside same wrapper, no separate blur/bg needed */}
        <div
          onMouseEnter={openMega}
          onMouseLeave={closeMega}
          className={`w-full overflow-hidden transition-all duration-300 ease-in-out ${
            megaOpen ? 'max-h-[600px] opacity-100 pointer-events-auto' : 'max-h-0 opacity-0 pointer-events-none'
          }`}
        >
          <div className="border-t border-b border-white/10">
            <div className="max-w-7xl mx-auto px-8 xl:px-10 py-8 xl:py-10 flex gap-10 xl:gap-12 items-start">
              <div className="flex-1 flex gap-12 xl:gap-16">
                <div className="flex-1">
                  <div className="flex items-center gap-5 mb-10">
                    <div className="flex-1 h-[1px] bg-white/20" />
                    <span className="text-[12px] xl:text-[14px] tracking-[0.4em] uppercase text-white/80 font-glacial whitespace-nowrap">Extrait de Fragrance</span>
                    <div className="flex-1 h-[1px] bg-white/20" />
                  </div>
                  <div className="grid grid-cols-3 gap-6 xl:gap-8">
                    {megaMenuData.extraitDeParfum.columns.map((col) => (
                      <div key={col.heading}>
                        <p className="text-[10px] xl:text-[11px] tracking-[0.3em] uppercase text-white/30 font-glacial mb-3 pb-2 border-b border-white/10 font-bold">{col.heading}</p>
                        <ul className="space-y-2.5 xl:space-y-3">
                          {col.items.map((item) => (
                            <li key={item.name}>
                              <Link
                                href={item.href}
                                className="block text-white/65 tracking-[0.1em] text-[14px] xl:text-[15px] font-glacial transition-all duration-200 hover:text-white hover:translate-x-1.5"
                                onMouseEnter={() => setPreviewImage(item.image)}
                                onMouseLeave={() => setPreviewImage(DEFAULT_IMAGE)}
                                onClick={() => setMegaOpen(false)}
                              >
                                {item.name}
                              </Link>
                            </li>
                          ))}
                        </ul>
                      </div>
                    ))}
                  </div>
                </div>
                <div className="w-[1px] bg-white/10 self-stretch" />
                <div className="w-[200px] xl:w-[220px] flex-shrink-0">
                  <div className="flex items-center gap-3 mb-10">
                    <div className="flex-1 h-[1px] bg-white/20" />
                    <span className="text-[12px] xl:text-[14px] tracking-[0.4em] uppercase text-white/80 font-glacial whitespace-nowrap">Aroma Pod</span>
                    <div className="flex-1 h-[1px] bg-white/20" />
                  </div>
                  {megaMenuData.aromaPod.columns.map((col) => (
                    <div key={col.heading}>
                      <p className="text-[10px] xl:text-[11px] tracking-[0.3em] uppercase text-white/30 font-glacial mb-3 pb-2 border-b border-white/10 font-bold">{col.heading}</p>
                      <ul className="space-y-2.5 xl:space-y-3">
                        {col.items.map((item) => (
                          <li key={item.name}>
                            <Link
                              href={item.href}
                              className="block text-white/65 tracking-[0.1em] text-[14px] xl:text-[15px] font-glacial transition-all duration-200 hover:text-white hover:translate-x-1.5"
                              onMouseEnter={() => setPreviewImage(item.image)}
                              onMouseLeave={() => setPreviewImage(DEFAULT_IMAGE)}
                              onClick={() => setMegaOpen(false)}
                            >
                              {item.name}
                            </Link>
                          </li>
                        ))}
                      </ul>
                    </div>
                  ))}
                </div>
              </div>
              <div className="w-[160px] xl:w-[200px] flex-shrink-0 flex items-center justify-center">
                <div className="relative w-full h-[240px] xl:h-[260px] overflow-hidden rounded-sm">
                  <Image key={previewImage} src={previewImage} alt="Preview" fill className="object-cover animate-imgfade" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* MOBILE NAV DRAWER */}
      <div className={`fixed inset-0 z-[150] lg:hidden transition-all duration-500 ${mobileNavOpen ? 'visible' : 'invisible'}`}>
        <div className={`absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity duration-500 ${mobileNavOpen ? 'opacity-100' : 'opacity-0'}`} onClick={() => setMobileNavOpen(false)} />
        <div className={`absolute top-0 left-0 h-full w-[80vw] max-w-xs ${BG} backdrop-blur-xl shadow-2xl flex flex-col transition-transform duration-500 ease-[cubic-bezier(0.23,1,0.32,1)] ${mobileNavOpen ? 'translate-x-0' : '-translate-x-full'}`}>
          <div className="flex items-center justify-between px-6 py-5 border-b border-white/10">
            <div className="relative w-20 h-6">
              <Link href="/" className="relative block w-24 sm:w-28 md:w-32 h-7 sm:h-8 md:h-10">
                <Image src="/logo-white.png" alt="Logo" fill className="object-contain object-left" />
              </Link>
            </div>
            <button onClick={() => setMobileNavOpen(false)} className="text-white/50 hover:text-white hover:rotate-90 transition-all duration-300 p-1">
              <Icon name="close" className="text-2xl" />
            </button>
          </div>
          <nav className="flex-1 min-h-0 overflow-y-auto px-6 py-6 space-y-1">
            <Link href="/about" className="block text-white text-[16px] font-glacial py-3 border-b border-white/5 tracking-widest">About</Link>
            <div className="border-b border-white/5">
              <button onClick={() => setMobilePerfumOpen(!mobilePerfumOpen)} className="w-full flex items-center justify-between text-white text-[16px] font-glacial py-3 tracking-widest">
                Fragrance
                <ChevronDown className={`w-4 h-4 transition-transform duration-300 ${mobilePerfumOpen ? 'rotate-180' : ''}`} />
              </button>
              <div className={`overflow-y-auto transition-all duration-300 ${mobilePerfumOpen ? 'max-h-[60vh] pb-4' : 'max-h-0'}`}>
                <div className="mt-3 mb-1">
                  <p className="text-[10px] tracking-[0.4em] uppercase text-white/30 font-glacial mb-3 px-2">Extrait de Parfum</p>
                  {megaMenuData.extraitDeParfum.columns.map((col) => (
                    <div key={col.heading} className="mt-3">
                      <p className="text-[11px] tracking-[0.3em] uppercase text-white/40 font-glacial mb-2 px-2 font-bold">{col.heading}</p>
                      <ul className="space-y-1">
                        {col.items.map((item) => (
                          <li key={item.name}>
                            <Link href={item.href} className="block text-white/65 text-[15px] font-glacial py-1.5 px-2 rounded hover:text-white transition-all" onClick={() => setMobileNavOpen(false)}>
                              {item.name}
                            </Link>
                          </li>
                        ))}
                      </ul>
                    </div>
                  ))}
                </div>
                <div className="my-4 mx-2 h-[1px] bg-white/10" />
                <div>
                  <p className="text-[10px] tracking-[0.4em] uppercase text-white/30 font-glacial mb-3 px-2">Aroma Pod</p>
                  {megaMenuData.aromaPod.columns.map((col) => (
                    <div key={col.heading} className="mt-3">
                      <p className="text-[11px] tracking-[0.3em] uppercase text-white/40 font-glacial mb-2 px-2 font-bold">{col.heading}</p>
                      <ul className="space-y-1">
                        {col.items.map((item) => (
                          <li key={item.name}>
                            <Link href={item.href} className="block text-white/65 text-[15px] font-glacial py-1.5 px-2 rounded hover:text-white transition-all" onClick={() => setMobileNavOpen(false)}>
                              {item.name}
                            </Link>
                          </li>
                        ))}
                      </ul>
                    </div>
                  ))}
                </div>
              </div>
            </div>
            <button onClick={() => { setMobileNavOpen(false); setIsPerfumeFinderOpen(true); }} className="w-full text-left block text-white text-[16px] font-glacial py-3 border-b border-white/5 tracking-widest">Discover Your Signature Fragrance</button>
            <Link href="/contact" className="block text-white text-[16px] font-glacial py-3 border-b border-white/5 tracking-widest">Contact Us</Link>
          </nav>
        </div>
      </div>

      {/* SIDEBAR */}
      <div className={`fixed inset-0 z-[100] transition-all duration-500 ${isMenuOpen ? 'visible' : 'invisible'}`}>
        <div className={`absolute inset-0 bg-black/80 backdrop-blur-md transition-opacity duration-500 ${isMenuOpen ? 'opacity-100' : 'opacity-0'}`} onClick={() => setIsMenuOpen(false)} />
        <div className={`absolute top-0 right-0 h-full w-full max-w-sm bg-zinc-950/85 backdrop-blur-xl shadow-2xl transition-transform duration-700 ease-[cubic-bezier(0.23,1,0.32,1)] p-10 flex flex-col ${isMenuOpen ? 'translate-x-0' : 'translate-x-full'}`}>
          <button onClick={() => setIsMenuOpen(false)} className="self-end p-2 text-white/50 hover:text-white hover:rotate-90 transition-all duration-300"><Icon name="close" className="text-3xl" /></button>
          <div className="flex-1 flex flex-col justify-center items-center text-center space-y-16">
            <div className="space-y-6">
              <h4 className="text-[11px] tracking-[0.5em] uppercase text-zinc-500 font-glacial font-bold">Contact Us</h4>
              <div className="space-y-4 text-xl font-light tracking-[0.1em] text-white font-glacial">
                <p className="cursor-pointer transition-colors hover:text-[#ffdda5]">+91 123 456 7890</p>
                <p className="cursor-pointer transition-colors hover:text-[#ffdda5]">info@grayfragrance.com</p>
                <p className="text-zinc-500 text-[11px] leading-relaxed max-w-[250px] mx-auto mt-4 tracking-[0.2em] uppercase">123 Luxury Lane, Fashion District, Mumbai, India.</p>
              </div>
            </div>
            <div className="space-y-8">
              <h4 className="text-[11px] tracking-[0.5em] uppercase text-zinc-500 font-glacial font-bold">Follow Us</h4>
              <div className="flex flex-row items-center justify-center gap-6">
                {[Facebook, Instagram, MessageCircle, Twitter, Linkedin].map((Icon2, idx) => (
                  <a key={idx} href="#" className="text-white transition-all duration-300 hover:-translate-y-1 hover:text-[#ffdda5]"><Icon2 className="w-5 h-5" /></a>
                ))}
              </div>
            </div>
          </div>
          <div className="pt-8 text-center border-t border-white/5">
            <p className="text-[10px] text-zinc-600 tracking-[0.4em] uppercase font-glacial">© 2026 Gray Fragrance</p>
          </div>
        </div>
      </div>

      <style jsx global>{`
        @keyframes imgfade {
          0%   { opacity: 0; transform: scale(1.06); }
          100% { opacity: 1; transform: scale(1);    }
        }
        .animate-imgfade {
          animation: imgfade 0.45s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
      `}</style>
    </>
  );
}