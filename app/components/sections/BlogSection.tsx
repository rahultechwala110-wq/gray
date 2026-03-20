'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { motion } from 'framer-motion';
import { ArrowUpRight } from 'lucide-react';

interface BlogPost {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  image: string;
  created_at: string;
  category: string;
}

interface BlogSettings {
  heading: string;
  subheading: string;
}

const fallbackPosts: BlogPost[] = [
  { id: 1, title: 'Does serum contain lightweight formula?',  slug: '/blog-details', excerpt: 'Cosmetic Creams Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea...', image: '/blog/1.png', created_at: '2020-10-09', category: '' },
  { id: 2, title: 'Treat your skin with organic products',     slug: '/blog-details', excerpt: 'Cosmetic Creams Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea...', image: '/blog/2.jpg', created_at: '2020-12-07', category: '' },
  { id: 3, title: 'Interesting ingredients used in cosmetic',  slug: '/blog-details', excerpt: 'Cosmetic Creams Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea...', image: '/blog/3.jpg', created_at: '2020-11-09', category: '' },
];

const fallbackSettings: BlogSettings = {
  heading:    'Latest Cosmetic News',
  subheading: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
};

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

export default function BlogSection() {
  const [posts, setPosts]       = useState<BlogPost[]>(fallbackPosts);
  const [settings, setSettings] = useState<BlogSettings>(fallbackSettings);

  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/blog.php`, { cache: 'no-store' })
      .then(r => r.json())
      .then(({ posts: p, settings: s }) => {
        if (p?.length)  setPosts(p);
        if (s?.heading) setSettings(s);
      })
      .catch(() => {});
  }, []);

  const featured = posts[0];
  const rest     = posts.slice(1, 3);

  return (
    <section className="min-h-screen mb-10 md:mb-25 flex flex-col justify-center bg-[#F9F6F1] relative overflow-hidden py-8 md:py-14">
      <div className="max-w-7xl mx-auto px-6 md:px-12 w-full relative z-10">

        {/* Header */}
        <div className="text-center mb-12 space-y-3">
          <h2 className="text-4xl md:text-5xl font-qlassy text-[#1A1A1A] mb-3 tracking-wide leading-none">
            {settings.heading}
          </h2>
          <p className="text-lg text-gray-600 tracking-wider font-glacial">
            {settings.subheading}
          </p>
        </div>

        {/* Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-12 items-start pt-10">

          {/* LEFT: Featured Blog */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="flex flex-col"
          >
            <Link href={featured.slug || '/blog-details'} className="relative w-full h-[450px] md:h-[520px] overflow-hidden shadow-sm group block mb-7">
              {featured.image ? (
                <img
                  src={featured.image}
                  alt={featured.title}
                  className="absolute inset-0 w-full h-full object-cover transition-all duration-700 group-hover:grayscale"
                />
              ) : (
                <div className="w-full h-full bg-[#EDE0C8]" />
              )}
            </Link>

            <div className="space-y-4">
              <div className="flex items-center gap-4">
                <span className="w-10 h-[1px] bg-gray-300" />
                <p className="text-[12px] text-gray-400 uppercase tracking-wider">{formatDate(featured.created_at)}</p>
              </div>
              <h3 className="text-2xl md:text-3xl text-gray-900 leading-tight tracking-tight font-qlassy hover:text-gray-500 transition-colors cursor-pointer">
                {featured.title}
              </h3>
              <p className="text-sm md:text-base text-gray-700 dark:text-gray-300 leading-relaxed line-clamp-2 max-w-sm font-glacial">
                {featured.excerpt}
              </p>
              <Link href={featured.slug || '/blog-details'} className="inline-flex items-center gap-3 group pt-2">
                <span className="text-[12px] tracking-[0.2em] uppercase font-qlassy font-bold text-gray-900 border-b border-black/20 pb-0.5 group-hover:border-black transition-colors duration-300">
                  Read More
                </span>
                <ArrowUpRight size={14} strokeWidth={2} className="text-gray-400 group-hover:text-gray-900 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-300" />
              </Link>
            </div>
          </motion.div>

          {/* RIGHT: Two Stacked Blogs */}
          <div className="flex flex-col gap-10 lg:gap-12">
            {rest.map((blog, idx) => (
              <motion.div
                key={blog.id}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: idx * 0.15 }}
                className="grid grid-cols-1 sm:grid-cols-[320px_1fr] gap-7 items-center"
              >
                <Link href={blog.slug || '/blog-details'} className="relative w-full aspect-[4/5] sm:aspect-square overflow-hidden shadow-sm group block flex-shrink-0">
                  {blog.image ? (
                    <img
                      src={blog.image}
                      alt={blog.title}
                      className="absolute inset-0 w-full h-full object-cover transition-all duration-700 group-hover:grayscale"
                    />
                  ) : (
                    <div className="w-full h-full bg-[#EDE0C8]" />
                  )}
                </Link>

                <div className="space-y-3">
                  <div className="flex items-center gap-3">
                    <span className="w-8 h-[1px] bg-gray-300" />
                    <p className="text-[11px] text-gray-400 uppercase tracking-widest">{formatDate(blog.created_at)}</p>
                  </div>
                  <h3 className="text-xl md:text-2xl text-gray-900 leading-tight tracking-tight font-qlassy hover:text-gray-500 transition-colors cursor-pointer">
                    {blog.title}
                  </h3>
                  <p className="text-sm md:text-base text-gray-700 dark:text-gray-300 leading-relaxed line-clamp-2 font-glacial">
                    {blog.excerpt}
                  </p>
                  <Link href={blog.slug || '/blog-details'} className="inline-flex items-center gap-3 group pt-2">
                    <span className="text-[12px] tracking-[0.2em] uppercase font-qlassy font-bold text-gray-900 border-b border-black/20 pb-0.5 group-hover:border-black transition-colors duration-300">
                      Read More
                    </span>
                    <ArrowUpRight size={14} strokeWidth={2} className="text-gray-400 group-hover:text-gray-900 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-300" />
                  </Link>
                </div>
              </motion.div>
            ))}
          </div>

        </div>
      </div>
    </section>
  );
}