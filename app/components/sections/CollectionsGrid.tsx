'use client';

import { useState, useEffect, useCallback } from 'react';
import useEmblaCarousel from 'embla-carousel-react';
import Autoplay from 'embla-carousel-autoplay';
import { ArrowUpRight } from 'lucide-react';

interface HeroData {
  title: string;
  btn_text: string;
  btn_link: string;
  video_file: string;
  updated_at: string;
}

interface Product {
  id?: number;
  name: string;
  image: string;
  link: string;
  btn_text: string;
}

const fallbackHero: HeroData = {
  title: 'New Arrivals',
  btn_text: 'Shop Collection',
  btn_link: '#',
  video_file: '',
  updated_at: '',
};

const fallbackProducts: Product[] = [
  { name: 'B612',     image: '/products/gray-b612.jpg', link: '#', btn_text: 'Shop Essential' },
  { name: 'Bulge',    image: '/products/gray-b613.jpg', link: '#', btn_text: 'Shop Essential' },
  { name: 'Brahe',    image: '/products/gray-b614.jpg', link: '#', btn_text: 'Shop Essential' },
  { name: 'Glase',    image: '/products/gray-b614.jpg', link: '#', btn_text: 'Shop Essential' },
  { name: 'Ganymade', image: '/products/gray-b614.jpg', link: '#', btn_text: 'Shop Essential' },
];

export default function CollectionsGrid() {
  const [hero, setHero]         = useState<HeroData>(fallbackHero);
  const [products, setProducts] = useState<Product[]>(fallbackProducts);
  const [videoKey, setVideoKey] = useState<string>('');

  useEffect(() => {
    fetch('/api/collections', { cache: 'no-store' })
      .then(r => r.json())
      .then(({ hero: h, products: p }) => {
        if (h) {
          setHero(h);
          setVideoKey(h.updated_at || Date.now().toString());
        }
        if (p?.length) {
          setProducts(p.map((item: Product) => ({
            ...item,
            image: item.image || '',  // route.ts থেকে already full URL আসছে
          })));
        }
      })
      .catch(() => {});
  }, []);

  const [emblaRef, emblaApi] = useEmblaCarousel(
    { loop: true, dragFree: false },
    [Autoplay({ delay: 3000, stopOnInteraction: false, stopOnMouseEnter: true })]
  );
  const [selectedIndex, setSelectedIndex] = useState(0);

  const onSelect = useCallback(() => {
    if (!emblaApi) return;
    setSelectedIndex(emblaApi.selectedScrollSnap());
  }, [emblaApi]);

  useEffect(() => {
    if (!emblaApi) return;
    onSelect();
    emblaApi.on('select', onSelect);
  }, [emblaApi, onSelect]);

  const scrollTo     = (i: number) => emblaApi?.scrollTo(i);
  const stopAutoplay = useCallback(() => emblaApi?.plugins()?.autoplay?.stop(), [emblaApi]);
  const playAutoplay = useCallback(() => emblaApi?.plugins()?.autoplay?.play(), [emblaApi]);
  const videoSrc = hero.video_file
    ? `${hero.video_file}?v=${videoKey}`
    : `/new-arrival.mp4?v=${videoKey}`;

  return (
    <section className="py-10 md:py-20 px-6 md:px-16 lg:px-20 bg-[#F9F6F1]">
      <div className="max-w-7xl mx-auto flex justify-end">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start w-full md:max-w-[102%] md:pl-4">

          {/* LEFT CARD — Video */}
          <div className="relative group overflow-hidden w-full">
            <div className="aspect-[4/5] relative bg-gray-200">
              <video
                key={videoSrc}
                autoPlay loop muted playsInline
                className="absolute inset-0 w-full h-full object-cover"
              >
                <source src={videoSrc} type="video/mp4" />
              </video>
              <div className="absolute inset-x-0 bottom-0 h-1/3 z-10 bg-gradient-to-t from-black/50 to-transparent" />
              <div className="absolute inset-x-0 bottom-12 text-center text-white z-20">
                <h3 className="text-4xl md:text-5xl font-qlassy mb-6 tracking-tight">
                  {hero.title}
                </h3>
                <a href={hero.btn_link} className="inline-flex items-center gap-3 group">
                  <span className="text-[12px] tracking-[0.2em] uppercase font-qlassy font-bold text-white border-b border-white/20 pb-0.5 group-hover:border-white transition-colors duration-300">
                    {hero.btn_text}
                  </span>
                  <ArrowUpRight size={14} strokeWidth={2} className="text-white/60 group-hover:text-white group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-300" />
                </a>
              </div>
            </div>
          </div>

          {/* RIGHT CARD — Carousel */}
          <div className="flex flex-col items-center w-full">
            <div className="w-full max-w-xl mb-4 md:mb-6">
              <div className="overflow-hidden aspect-[4/4.2] relative touch-pan-y group"
                   ref={emblaRef} onMouseEnter={stopAutoplay} onMouseLeave={playAutoplay}>
                <div className="flex h-full">
                  {products.map((product, index) => (
                    <div className="relative flex-[0_0_100%] min-w-0" key={index}>
                      <img
                        src={product.image}
                        alt={product.name}
                        className="absolute inset-0 w-full h-full object-cover shadow-sm transition-transform duration-1000"
                      />
                    </div>
                  ))}
                </div>

                {/* Dots */}
                <div className="absolute left-6 bottom-6 z-30 flex items-center gap-2">
                  {products.map((_, index) => (
                    <button key={index} onClick={() => scrollTo(index)}
                            className="flex items-center justify-center py-2"
                            aria-label={`Go to slide ${index + 1}`}>
                      <div className={`rounded-full transition-all duration-300 ${
                        selectedIndex === index ? "w-6 h-1 bg-black" : "w-1.5 h-1.5 bg-gray-400/60 hover:bg-black/40"
                      }`} />
                    </button>
                  ))}
                </div>
                <div className="absolute right-6 bottom-6 z-30 text-[10px] tracking-widest text-black/40 font-medium">
                  {selectedIndex + 1} / {products.length}
                </div>
              </div>
            </div>

            <div className="text-center">
              <h3 className="text-4xl font-qlassy mb-2 tracking-tight text-gray-800">
                {products[selectedIndex]?.name}
              </h3>
              <a href={products[selectedIndex]?.link || '#'} className="inline-flex items-center gap-3 group">
                <span className="text-[12px] tracking-[0.2em] uppercase font-qlassy font-bold text-gray-900 dark:text-white border-b border-black/20 dark:border-white/20 pb-0.5 group-hover:border-black dark:group-hover:border-white transition-colors duration-300">
                  {products[selectedIndex]?.btn_text || 'Shop Essential'}
                </span>
                <ArrowUpRight size={14} strokeWidth={2} className="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-300" />
              </a>
            </div>
          </div>

        </div>
      </div>
    </section>
  );
}