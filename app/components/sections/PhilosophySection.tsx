'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { ArrowUpRight } from 'lucide-react';

interface AboutHomeData {
  label: string;
  heading: string;
  description: string;
  btn_text: string;
  btn_link: string;
  small_image: string;
  large_image: string;
}

const defaultData: AboutHomeData = {
  label: 'About Us',
  heading: 'Lorem ipsum dolor sit amet,\nconsectetur adipiscing elit,',
  description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Quis ipsum suspendisse ultrices gravida. Risus commodo viverra maecenas accumsan lacus vel',
  btn_text: 'Our Story',
  btn_link: '/about',
  small_image: '',
  large_image: '',
};

export default function PhilosophySection() {
  const [data, setData] = useState<AboutHomeData>(defaultData);

  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/about-home.php`)
      .then(r => r.json())
      .then(d => setData(d))
      .catch(() => {});
  }, []);

  const smallImgSrc = data.small_image ? data.small_image : '/about/small-image.jpg';
  const largeImgSrc = data.large_image ? data.large_image : '/about/large-image.jpg';
  const headingLines = data.heading.split('\n');

  return (
    <section className="relative pt-0 pb-12 md:py-12 px-6 md:px-20 bg-[#F9F6F1] dark:bg-zinc-950 min-h-screen flex items-center overflow-hidden">

      {/* Watermark Logo */}
      <div className="absolute right-0 top-1/2 -translate-y-1/2 opacity-5 pointer-events-none select-none">
        <Image
          src="/watermark.png"
          alt="Watermark Logo"
          width={600}
          height={600}
          className="object-contain"
        />
      </div>

      <div className="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-16 items-stretch relative z-10">

        {/* LEFT COLUMN */}
        <div className="flex flex-col justify-between space-y-12">
          <div className="space-y-4">
            <span className="text-[13px] tracking-[0.4em] uppercase font-bold text-gray-600 block mb-4 font-qlassy">
              {data.label}
            </span>
            <h2 className="text-xl md:text-xl font-bold uppercase leading-tight tracking-tight text-gray-700 font-glacial">
              {headingLines.map((line, i) => (
                <span key={i}>
                  {line}
                  {i < headingLines.length - 1 && <br />}
                </span>
              ))}
            </h2>
          </div>

          <div className="relative w-full flex justify-center md:justify-end md:pr-0 pb-0">
            <div className="relative w-full max-w-[400px] aspect-[4/5] shadow-sm overflow-hidden">
              <img src={smallImgSrc} alt="Product detail" className="object-cover w-full h-full" />
            </div>
          </div>
        </div>

        {/* RIGHT COLUMN */}
        <div className="space-y-8 flex flex-col items-center md:items-start">
          <div className="relative w-full aspect-square md:aspect-[4/5] max-w-md overflow-hidden">
            <img src={largeImgSrc} alt="Atmospheric product" className="object-cover w-full h-full" />
          </div>

          <div className="max-w-md space-y-6">
            <p className="text-[17px] text-gray-600 font-glacial leading-relaxed tracking-wide">
              {data.description}
            </p>

            <Link href={data.btn_link} className="inline-flex items-center gap-3 group">
              <span className="text-[12px] tracking-[0.2em] uppercase font-qlassy font-bold text-gray-900 dark:text-white border-b border-black/20 dark:border-white/20 pb-0.5 group-hover:border-black dark:group-hover:border-white transition-colors duration-300">
                {data.btn_text}
              </span>
              <ArrowUpRight
                size={14}
                strokeWidth={2}
                className="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-300"
              />
            </Link>
          </div>
        </div>

      </div>
    </section>
  );
}