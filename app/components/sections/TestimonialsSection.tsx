"use client";

import React, { useState, useEffect } from "react";

interface Testimonial {
  id: number;
  client_name: string;
  company: string;
  review: string;
  rating: number;
}

const fallbackTestimonials: Testimonial[] = [
  { id: 1, client_name: "Victoria Wotton", company: "Fermentum Odio Co.", rating: 5,
    review: "Eget mauris pharetra et ultrices neque ornare. Leo integer malesuada nunc sit vel. A arcu cursus vitae congue mauris rhoncus aenean vel elit. Morbi non arcu risus quis varius." },
  { id: 2, client_name: "Victoria Wotton", company: "Fermentum Odio Co.", rating: 5,
    review: "Eget mauris pharetra et ultrices neque ornare. Leo integer malesuada nunc sit vel. A arcu cursus vitae congue mauris rhoncus aenean vel elit. Morbi non arcu risus quis varius." },
  { id: 3, client_name: "Victoria Wotton", company: "Fermentum Odio Co.", rating: 5,
    review: "Eget mauris pharetra et ultrices neque ornare. Leo integer malesuada nunc sit vel. A arcu cursus vitae congue mauris rhoncus aenean vel elit. Morbi non arcu risus quis varius." },
  { id: 4, client_name: "Victoria Wotton", company: "Fermentum Odio Co.", rating: 5,
    review: "Eget mauris pharetra et ultrices neque ornare. Leo integer malesuada nunc sit vel. A arcu cursus vitae congue mauris rhoncus aenean vel elit. Morbi non arcu risus quis varius." },
];

export default function TestimonialsSection() {
  const [testimonials, setTestimonials] = useState<Testimonial[]>(fallbackTestimonials);

  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/testimonials.php`, { cache: 'no-store' })
      .then(r => r.json())
      .then((data: Testimonial[]) => {
        if (data?.length) setTestimonials(data);
      })
      .catch(() => {});
  }, []);

  // 3 se zyada hone par hi auto-scroll
  const shouldScroll = testimonials.length > 3;

  return (
    <section className="relative pt-6 pb-12 md:py-16 bg-[#F9F6F1] overflow-hidden">

      <div className="max-w-7xl mx-auto px-6">
        <h2 className="text-4xl md:text-5xl text-center mb-12 text-[#1A1A1A] font-qlassy tracking-tight">
          Testimonials
        </h2>
      </div>

      {shouldScroll ? (
        /* ── AUTO-SCROLL MARQUEE (3+ testimonials) ── */
        <div
          className="relative w-full flex overflow-hidden py-8"
          style={{
            WebkitMaskImage: "linear-gradient(to right, transparent, black 10%, black 90%, transparent)",
            maskImage:       "linear-gradient(to right, transparent, black 10%, black 90%, transparent)",
          }}
        >
          <div className="flex w-max animate-scroll-rtl hover:[animation-play-state:paused]">
            {[1, 2].map((group) => (
              <div key={group} className="flex gap-10 pr-10">
                {testimonials.map((t) => (
                  <TestimonialCard key={`${group}-${t.id}`} t={t} />
                ))}
              </div>
            ))}
          </div>
        </div>
      ) : (
        /* ── STATIC GRID (1-3 testimonials) ── */
        <div className="max-w-7xl mx-auto px-6 py-8">
          <div className={`grid gap-8 ${
            testimonials.length === 1 ? 'grid-cols-1 max-w-lg mx-auto' :
            testimonials.length === 2 ? 'grid-cols-1 md:grid-cols-2 max-w-3xl mx-auto' :
            'grid-cols-1 md:grid-cols-3'
          }`}>
            {testimonials.map((t) => (
              <TestimonialCard key={t.id} t={t} />
            ))}
          </div>
        </div>
      )}

      <style dangerouslySetInnerHTML={{ __html: `
        @keyframes scrollRightToLeft {
          0%   { transform: translateX(0%); }
          100% { transform: translateX(-50%); }
        }
        .animate-scroll-rtl {
          animation: scrollRightToLeft 30s linear infinite;
        }
      ` }} />
    </section>
  );
}

function TestimonialCard({ t }: { t: Testimonial }) {
  return (
    <div className="w-[350px] md:w-[400px] flex-shrink-0 p-10 rounded-3xl bg-[#F3EDE4] shadow-md transition-shadow duration-300 hover:shadow-xl cursor-pointer">
      {/* Review text — word-wrap fix */}
      <p
        className="text-md leading-relaxed mb-6 text-gray-700 font-glacial"
        style={{ wordBreak: 'break-word', overflowWrap: 'break-word', whiteSpace: 'normal' }}
      >
        {t.review}
      </p>
      <div className="mt-auto">
        <h5 className="text-lg text-black font-qlassy uppercase tracking-wider font-bold">
          {t.client_name}
        </h5>
        <p className="text-sm text-gray-500 font-glacial">
          {t.company}
        </p>
      </div>
    </div>
  );
}