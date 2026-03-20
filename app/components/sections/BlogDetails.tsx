'use client';

import { useState } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { ArrowLeft, ArrowUpRight } from 'lucide-react';

const relatedPosts = [
  {
    id: 1,
    date: 'DECEMBER 7, 2020',
    title: 'Treat your skin with organic products',
    excerpt: 'Cosmetic Creams Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea...',
    image: '/blog/2.jpg',
  },
  {
    id: 2,
    date: 'NOVEMBER 9, 2020',
    title: 'Interesting ingredients used in cosmetic',
    excerpt: 'Cosmetic Creams Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea...',
    image: '/blog/3.jpg',
  },
];

export default function BlogDetailPage() {
  const [hoveredRelated, setHoveredRelated] = useState<number | null>(null);

  return (
    <div className="min-h-screen" style={{ background: '#F9F6F1', color: '#1a1a1a' }}>

      {/* ── HERO ── */}
      <div className="relative w-full" style={{ height: '88vh', maxHeight: '700px' }}>
        <Image
          src="/blog/blog-banner.jpg"
          alt="Blog Hero"
          fill
          className="object-cover"
          priority
        />
        <div
          className="absolute inset-0"
          style={{ background: 'linear-gradient(to bottom, rgba(0,0,0,0.18) 0%, rgba(0,0,0,0.55) 60%, rgba(26,22,18,0.92) 100%)' }}
        />

        <div className="absolute top-8 left-8 z-10">
          <Link
            href="/blog"
            className="inline-flex items-center gap-2 font-glacial text-[10px] tracking-[0.4em] uppercase transition-opacity duration-200 hover:opacity-60"
            style={{ color: 'rgba(249,246,241,0.7)' }}
          >
            <ArrowLeft className="w-3.5 h-3.5" />
            Back to Journal
          </Link>
        </div>

        <div className="absolute bottom-0 left-0 right-0 px-8 sm:px-16 lg:px-24 pb-14 z-10">
          <div className="max-w-3xl">
            <div className="flex items-center gap-4 mb-5">
              <div className="w-8 h-[1px]" style={{ background: 'rgba(249,246,241,0.4)' }} />
              <span
                className="font-glacial text-[10px] tracking-[0.45em] uppercase"
                style={{ color: 'rgba(249,246,241,0.5)' }}
              >
                October 9, 2020
              </span>
            </div>

            <h1
              className="font-qlassy font-light leading-tight mb-4"
              style={{ fontSize: 'clamp(32px, 5vw, 58px)', color: '#F9F6F1', letterSpacing: '-0.01em', lineHeight: 1.15 }}
            >
              Does serum contain<br />lightweight formula?
            </h1>

            <div className="flex gap-3 mt-5">
              {['Fragrance', 'Skincare', 'Ingredients'].map((tag) => (
                <span
                  key={tag}
                  className="font-glacial text-[9px] tracking-[0.35em] uppercase px-3 py-1"
                  style={{ border: '1px solid rgba(249,246,241,0.25)', color: 'rgba(249,246,241,0.5)' }}
                >
                  {tag}
                </span>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* ── ARTICLE BODY ── */}
      <div className="max-w-7xl mx-auto px-6 sm:px-10 lg:px-16 py-20">
        <div className="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-16 xl:gap-24">

          <article>
            <p
              className="font-glacial font-light mb-10 leading-relaxed"
              style={{ fontSize: '20px', color: '#1a1a1a', letterSpacing: '0.01em', lineHeight: 1.75 }}
            >
              Cosmetic Creams Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
            </p>

            <div className="flex items-center gap-6 mb-10">
              <div className="w-8 h-[1px]" style={{ background: 'rgba(26,26,26,0.25)' }} />
              <span className="font-qlassy text-[11px] font-bold tracking-[0.5em] uppercase" style={{ color: 'rgba(26,26,26,0.38)' }}>
                The Science
              </span>
            </div>

            <p
              className="font-glacial mb-7 leading-loose"
              style={{ fontSize: '16px', color: 'rgba(26,26,26,0.72)', fontWeight: 300, lineHeight: 1.9 }}
            >
              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
            </p>

            <p
              className="font-glacial mb-12 leading-loose"
              style={{ fontSize: '16px', color: 'rgba(26,26,26,0.72)', fontWeight: 300, lineHeight: 1.9 }}
            >
              Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </p>

            <blockquote
              className="my-14 pl-8 font-glacial font-light leading-relaxed"
              style={{
                borderLeft: '2px solid rgba(26,26,26,0.18)',
                fontSize: '22px',
                color: '#1a1a1a',
                letterSpacing: '0.01em',
                lineHeight: 1.6,
              }}
            >
              "A fragrance's formula is as precise as a poem — every note placed with intention, every molecule chosen for memory."
            </blockquote>

            <div className="relative w-full my-12 overflow-hidden" style={{ height: '420px' }}>
              <Image src="/blog/blog-1.png" alt="Article image" fill className="object-cover" />
            </div>

            <div className="flex items-center gap-6 mb-10">
              <div className="w-8 h-[1px]" style={{ background: 'rgba(26,26,26,0.25)' }} />
              <span className="font-qlassy text-[11px] font-bold tracking-[0.5em] uppercase" style={{ color: 'rgba(26,26,26,0.38)' }}>
                Ingredients
              </span>
            </div>

            <p
              className="font-glacial mb-7 leading-loose"
              style={{ fontSize: '16px', color: 'rgba(26,26,26,0.72)', fontWeight: 300, lineHeight: 1.9 }}
            >
              Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.
            </p>

            {/* Prev / Next nav - Updated to Story Style */}
            <div
              className="flex items-center justify-between pt-10"
              style={{ borderTop: '1px solid rgba(26,26,26,0.1)' }}
            >
              <Link href="#" className="inline-flex items-center gap-3 group">
                {/* Pointing Top-Left */}
                <ArrowUpRight size={14} className="text-gray-400 -scale-x-100 group-hover:text-black group-hover:-translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-300" />
                <span className="text-[12px] tracking-[0.2em] uppercase font-glacial font-bold text-gray-900 border-b border-black/20 pb-0.5 group-hover:border-black transition-colors duration-300">
                  Previous Post
                </span>
              </Link>
              
              <Link href="#" className="inline-flex items-center gap-3 group">
                <span className="text-[12px] tracking-[0.2em] uppercase font-glacial font-bold text-gray-900 border-b border-black/20 pb-0.5 group-hover:border-black transition-colors duration-300">
                  Next Post
                </span>
                <ArrowUpRight size={14} className="text-gray-400 group-hover:text-black group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-300" />
              </Link>
            </div>
          </article>

          {/* Sidebar */}
          <aside className="space-y-12">
            <div>
              <div className="flex items-center gap-4 mb-6">
                <div className="w-6 h-[1px]" style={{ background: 'rgba(26,26,26,0.25)' }} />
                <p className="font-qlassy text-[11px] font-bold tracking-[0.5em] uppercase" style={{ color: 'rgba(26,26,26,0.38)' }}>
                  Written By
                </p>
              </div>
              <div className="flex items-center gap-4 mb-3">
                <div className="relative w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                  <Image src="/mega-menu/11.jpg" alt="Author" fill className="object-cover" />
                </div>
                <div>
                  <p className="font-glacial text-[13px] font-light" style={{ color: '#1a1a1a' }}>Admin</p>
                  <p className="font-glacial text-[10px] tracking-[0.2em]" style={{ color: 'rgba(26,26,26,0.38)' }}>Senior Perfumer</p>
                </div>
              </div>
            </div>

            <div className="h-[1px]" style={{ background: 'rgba(26,26,26,0.08)' }} />

            <div>
              <div className="flex items-center gap-4 mb-6">
                <div className="w-6 h-[1px]" style={{ background: 'rgba(26,26,26,0.25)' }} />
                <p className="font-qlassy text-[11px] font-bold tracking-[0.5em] uppercase" style={{ color: 'rgba(26,26,26,0.38)' }}>
                  Categories
                </p>
              </div>
              <ul className="space-y-3">
                {['Fragrance Notes', 'Ingredient Stories', 'Behind the Bottle', 'Seasonal Edits', 'The Archive'].map((cat) => (
                  <li key={cat}>
                    <Link
                      href="#"
                      className="flex items-center justify-between font-glacial text-[13px] tracking-[0.15em] pb-3 transition-colors group"
                      style={{
                        borderBottom: '1px solid rgba(26,26,26,0.07)',
                        color: 'rgba(26,26,26,0.55)',
                      }}
                      onMouseEnter={(e) => ((e.currentTarget as HTMLAnchorElement).style.color = '#1a1a1a')}
                      onMouseLeave={(e) => ((e.currentTarget as HTMLAnchorElement).style.color = 'rgba(26,26,26,0.55)')}
                    >
                      {cat}
                      <ArrowUpRight className="w-3 h-3 opacity-0 group-hover:opacity-100 transition-all group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                    </Link>
                  </li>
                ))}
              </ul>
            </div>

            <div className="h-[1px]" style={{ background: 'rgba(26,26,26,0.08)' }} />

            <div>
              <div className="flex items-center gap-4 mb-6">
                <div className="w-6 h-[1px]" style={{ background: 'rgba(26,26,26,0.25)' }} />
                <p className="font-qlassy text-[11px] font-bold tracking-[0.5em] uppercase" style={{ color: 'rgba(26,26,26,0.38)' }}>
                  Featured
                </p>
              </div>
              <div
                className="relative overflow-hidden group cursor-pointer"
                style={{ background: '#1a1a1a' }}
              >
                <div className="relative h-52">
                  <Image src="/mega-menu/01.jpg" alt="Brave" fill className="object-cover opacity-60 group-hover:opacity-40 transition-opacity duration-500 group-hover:scale-105" />
                </div>
                <div className="p-5">
                  <p className="font-glacial text-[9px] tracking-[0.4em] uppercase mb-1" style={{ color: 'rgba(249,246,241,0.35)' }}>Man</p>
                  <p className="font-glacial text-lg font-light" style={{ color: '#F9F6F1' }}>Brave</p>
                  <p className="font-glacial text-[11px] tracking-[0.2em] mb-4" style={{ color: 'rgba(249,246,241,0.4)' }}>100ml EDP</p>
                  
                  {/* Shop Now with Story Style */}
                  <Link href="#" className="inline-flex items-center gap-3 group">
                    <span className="text-[11px] tracking-[0.2em] uppercase font-glacial font-bold text-white border-b border-white/20 pb-0.5 group-hover:border-white transition-colors duration-300">
                      Shop Now
                    </span>
                    <ArrowUpRight size={13} className="text-white/40 group-hover:text-white group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-300" />
                  </Link>
                </div>
              </div>
            </div>
          </aside>
        </div>

        {/* ── RELATED POSTS ── */}
        <div className="mt-24 pt-16" style={{ borderTop: '1px solid rgba(26,26,26,0.1)' }}>
          <div className="flex items-center gap-6 mb-12">
            <div className="w-8 h-[1px]" style={{ background: 'rgba(26,26,26,0.25)' }} />
            <p className="font-qlassy text-[11px] font-bold tracking-[0.5em] uppercase" style={{ color: 'rgba(26,26,26,0.38)' }}>
              Related Articles
            </p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-10">
            {relatedPosts.map((post) => (
              <div
                key={post.id}
                className="group block cursor-pointer"
                onMouseEnter={() => setHoveredRelated(post.id)}
                onMouseLeave={() => setHoveredRelated(null)}
              >
                <div className="relative overflow-hidden mb-6" style={{ height: '400px', width: '100%' }}>
                  <Image
  src={post.image}
  alt={post.title}
  fill
  className="object-cover transition-all duration-700 group-hover:grayscale"
/>
                </div>

                <div className="flex items-center gap-4 mb-4">
                  <div
                    className="h-[1px] transition-all duration-300"
                    style={{
                      // width: hoveredRelated === post.id ? '32px' : '20px',
                      // background: 'rgba(26,26,26,0.3)',
                    }}
                  />
                  <div className="flex items-center gap-4">
                <span className="w-10 h-[1px] bg-gray-300" />
                <p className="text-[12px] text-gray-400 uppercase tracking-wider">{post.date}</p>
              </div>
                </div>

                <h3
                  className="text-2xl md:text-3xl text-gray-900 leading-tight tracking-tight font-glacial hover:text-gray-500 transition-colors cursor-pointer"
                  
                >
                  {post.title}
                </h3>

                <p
                  className="text-sm md:text-base text-gray-700 dark:text-gray-300 leading-relaxed line-clamp-2 max-w-sm font-glacial mb-2"
                >
                  {post.excerpt}
                </p>

                {/* Read More with Story Style */}
                <Link
                href="#"
                className="inline-flex items-center gap-3 group pt-2"
              >
                <span className="text-[12px] tracking-[0.2em] uppercase font-glacial font-bold text-gray-900 border-b border-black/20 pb-0.5 group-hover:border-black transition-colors duration-300">
                  Read More
                </span>
                <ArrowUpRight
                  size={14}
                  strokeWidth={2}
                  className="text-gray-400 group-hover:text-gray-900 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-300"
                />
              </Link>
              </div>
            ))}
          </div>
        </div>
      </div>

      <style jsx global>{`
        * { box-sizing: border-box; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #F9F6F1; }
        ::-webkit-scrollbar-thumb { background: rgba(26,26,26,0.12); border-radius: 2px; }
      `}</style>
    </div>
  );
}