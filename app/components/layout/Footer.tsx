"use client";

import Image from 'next/image';
import { Facebook, Instagram, Twitter, Linkedin, ArrowRight } from 'lucide-react';
import { useState } from 'react';

export default function Footer() {
  const [email, setEmail] = useState('');
  const [subscribed, setSubscribed] = useState(false);
  const [error, setError] = useState('');

  const handleSubscribe = () => {
    if (!email.trim()) {
      setError('Email is required.');
      return;
    }
    const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    if (!isValid) {
      setError('Please enter a valid email.');
      return;
    }
    setError('');
    setSubscribed(true);
    setEmail('');
  };

  return (
    <footer className="bg-[#F9F6F1] dark:bg-black pt-6 pb-16 px-6">
      <div className="max-w-7xl mx-auto flex flex-col items-center">

        {/* LOGO SECTION */}
        <div className="mb-8 flex flex-col items-center">
          <div className="relative w-20 h-20 mb-4">
            <Image
              src="/footer-logo.png"
              alt="GRAY Logo"
              fill
              className="object-contain dark:invert"
            />
          </div>

          <h3 className="text-3xl md:text-4xl font-qlassy text-center mb-4 tracking-tight">
            Shop Limited Edition.
          </h3>

          <p className="text-gray-500 text-center text-[12px] md:text-[13px] max-w-sm mx-auto leading-relaxed tracking-[0.15em] font-glacial">
            Crafted by nature, refined by time. Our collection represents the pinnacle of olfactory art.
          </p>
        </div>

        {/* PAYMENT ICONS */}
        <div className="flex gap-4 mb-8 grayscale opacity-30">
          {['VISA', 'MASTER', 'AMEX'].map((card) => (
            <div key={card} className="h-7 w-12 border border-black/20 dark:border-white/20 rounded flex items-center justify-center text-[8px] font-bold tracking-widest">
              {card}
            </div>
          ))}
        </div>

        {/* NEWSLETTER SECTION */}
        <div className="flex flex-col items-center mb-10 w-full">
          {!subscribed ? (
            <>
              <div className={`flex items-stretch w-full max-w-sm rounded-sm overflow-hidden transition-all duration-300 ${
                error
                  ? 'border border-red-300 dark:border-red-500/50'
                  : 'border border-black/10 dark:border-white/10 focus-within:border-black/30 dark:focus-within:border-white/30'
              }`}>
                <input
                  type="email"
                  value={email}
                  required
                  onChange={(e) => { setEmail(e.target.value); setError(''); }}
                  onKeyDown={(e) => e.key === 'Enter' && handleSubscribe()}
                  placeholder="Enter your email *"
                  className="flex-1 bg-transparent text-[11px] tracking-[0.15em] px-4 py-3 text-black dark:text-white placeholder:text-gray-300 dark:placeholder:text-gray-600 outline-none font-qlassy"
                />

                {/* ✅ Updated — matches cart checkout button style */}
                <button
                  onClick={handleSubscribe}
                  className="relative overflow-hidden px-5 py-3 bg-black dark:bg-white text-white dark:text-black flex items-center justify-between gap-3 text-[12px] tracking-[0.3em] uppercase font-glacial whitespace-nowrap group rounded-sm"
                >
                  <span
                    className="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-out"
                    style={{ background: '#2e2e2e' }}
                  />
                  <span className="relative z-10">Subscribe</span>
                  <ArrowRight
                    size={14}
                    className="relative z-10 transition-transform duration-300 group-hover:translate-x-2"
                  />
                </button>
              </div>

              {/* Error / hint */}
              <p className={`mt-2 text-[10px] tracking-[0.2em] font-glacial transition-opacity duration-200 ${
                error ? 'text-red-400 opacity-100' : 'text-gray-500 dark:text-gray-600 opacity-100'
              }`}>
              </p>
            </>
          ) : (
            <div className="flex items-center gap-3 py-3 px-6 border border-black/10 dark:border-white/10 rounded-sm max-w-sm w-full justify-center">
              <div className="w-1 h-1 rounded-full bg-black/40 dark:bg-white/40" />
              <p className="text-[10px] tracking-[0.3em] uppercase font-glacial text-gray-500 dark:text-gray-400">
                You&apos;re on the list
              </p>
              <div className="w-1 h-1 rounded-full bg-black/40 dark:bg-white/40" />
            </div>
          )}
        </div>

        {/* BOTTOM BAR */}
        <div className="w-full pt-8 border-t border-black/5 dark:border-white/5">
          <div className="grid grid-cols-1 md:grid-cols-3 items-center text-[10px] tracking-[0.25em] uppercase text-gray-500 gap-y-10">

            {/* Left: Links */}
            <div className="flex gap-8 justify-center order-2 md:order-1 whitespace-nowrap font-glacial">
              <a href="#" className="hover:text-black dark:hover:text-white transition-colors">Privacy</a>
              <a href="#" className="hover:text-black dark:hover:text-white transition-colors">Terms</a>
              <a href="/contact" className="hover:text-black dark:hover:text-white transition-colors">Contact</a>
            </div>

            {/* Center: Copyright */}
            <div className="flex justify-center order-1 md:order-2">
              <p className="font-glacial text-center whitespace-nowrap text-gray-400">
                © 2026 GRAY. All Rights Reserved.
              </p>
            </div>

            {/* Right: Social Icons */}
            <div className="flex gap-8 justify-center order-3">
              {[
                { Icon: Facebook, href: "#" },
                { Icon: Instagram, href: "#" },
                { Icon: Twitter, href: "#" },
                { Icon: Linkedin, href: "#" }
              ].map(({ Icon, href }, idx) => (
                <a key={idx} href={href} className="hover:text-black dark:hover:text-white transition-all transform hover:scale-125">
                  <Icon size={16} strokeWidth={1.2} />
                </a>
              ))}
            </div>

          </div>
        </div>

      </div>
    </footer>
  );
}