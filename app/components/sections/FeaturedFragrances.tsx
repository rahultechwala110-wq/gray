'use client';

import { useState, useEffect, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import useEmblaCarousel from 'embla-carousel-react';
import Autoplay from 'embla-carousel-autoplay';
import { ChevronLeft, ChevronRight, Plus, Minus, ShoppingBag, X } from 'lucide-react';
import FragranceCard from '../ui/FragranceCard';
import Link from 'next/link';

interface Category {
  id?: number;
  name: string;
  label: string;
}

interface Fragrance {
  id?: number;
  name: string;
  type: string;
  image: string;
  category: string;
  href: string;
  price?: number;
}

const fallbackCategories: Category[] = [
  { name: 'man',      label: 'Man' },
  { name: 'woman',    label: 'Woman' },
  { name: 'Aroma Pod',label: 'Aroma Pod' },
];

const fallbackProducts: Fragrance[] = [
  // Man
  { name: 'Brave',      type: 'Extrait De Parfum', category: 'man',       image: '/products/brave-men.jpg',        href: '/brave-mens-perfume-55ml',        price: 4200 },
  { name: 'Boss',       type: 'Extrait De Parfum', category: 'man',       image: '/products/boss-men.jpg',         href: '/boss-mens-perfume-55m',           price: 4500 },
  { name: 'Gentle',     type: 'Extrait De Parfum', category: 'man',       image: '/products/gentle-men.jpg',       href: '/gentle-mens-perfume-55ml',        price: 4200 },
  { name: 'Bold',       type: 'Extrait De Parfum', category: 'man',       image: '/products/bold-men.jpg',         href: '/gold-men-perfume-55ml',           price: 4800 },
  { name: 'Generous',   type: 'Extrait De Parfum', category: 'man',       image: '/products/generous.jpg',         href: '/generous-mens-perfume-55ml',      price: 4200 },
  { name: 'Groomed',    type: 'Extrait De Parfum', category: 'man',       image: '/products/groomed.jpg',          href: '/groomed-mens-perfume-55m',        price: 4200 },
  // Woman
  { name: 'Bliss',      type: 'Extrait De Parfum', category: 'woman',     image: '/products/bliss-woman.jpg',      href: '/bliss-womens-perfume-55ml',       price: 3500 },
  { name: 'Gorgeous',   type: 'Extrait De Parfum', category: 'woman',     image: '/products/gorgeous-woman.jpg',   href: '/gorgeous-womens-perfume-55ml',    price: 3800 },
  { name: 'Braveheart', type: 'Extrait De Parfum', category: 'woman',     image: '/products/braveheart-woman.jpg', href: '/braveheart-womens-perfume-55ml',  price: 4200 },
  { name: 'Glorious',   type: 'Extrait De Parfum', category: 'woman',     image: '/products/glorious-woman.jpg',   href: '/glorious-womens-perfume-55ml',    price: 4200 },
  { name: 'Brilliance', type: 'Extrait De Parfum', category: 'woman',     image: '/products/brilliance-woman.jpg', href: '/brilliance-womens-perfume-55ml',  price: 3800 },
  { name: 'Gifted',     type: 'Extrait De Parfum', category: 'woman',     image: '/products/gifted-woman.jpg',     href: '/gifted-womens-perfume-55ml',      price: 4200 },
  // Aroma Pod
  { name: 'B612',       type: 'Aroma Pod',          category: 'Aroma Pod', image: '/products/b612.jpg',             href: '/aroma-pod/b612',                  price: 2500 },
  { name: 'Bulge',      type: 'Aroma Pod',          category: 'Aroma Pod', image: '/products/bulge.jpg',            href: '/aroma-pod/bulge',                 price: 2500 },
  { name: 'Brahe',      type: 'Aroma Pod',          category: 'Aroma Pod', image: '/products/brahe.jpg',            href: '/aroma-pod/brahe',                 price: 2500 },
  { name: 'Glese',      type: 'Aroma Pod',          category: 'Aroma Pod', image: '/products/glese.jpg',            href: '/aroma-pod/glese',                 price: 2500 },
  { name: 'Ganymede',   type: 'Aroma Pod',          category: 'Aroma Pod', image: '/products/ganymede.jpg',         href: '/aroma-pod/ganymede',              price: 2500 },
  { name: 'Gaspra',     type: 'Aroma Pod',          category: 'Aroma Pod', image: '/products/gaspra.jpg',           href: '/aroma-pod/gaspra',                price: 2500 },
];

export default function FeaturedFragrances() {
  const [categories, setCategories] = useState<Category[]>(fallbackCategories);
  const [allFragrances, setAllFragrances] = useState<Fragrance[]>(fallbackProducts);
  const [selectedCategory, setSelectedCategory] = useState<string>('man');
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [expandedId, setExpandedId] = useState<string | null>(null);
  const [quantities, setQuantities] = useState<{ [key: string]: number }>({});

  useEffect(() => {
    fetch('/api/fragrances')
      .then(r => r.json())
      .then(({ categories: cats, products }) => {
        if (cats?.length) setCategories(cats);
        if (products?.length) {
          const mapped = products.map((p: Fragrance) => ({
            ...p,
            image: p.image || '',
          }));
          setAllFragrances(mapped);
          setSelectedCategory(cats?.[0]?.name || 'man');
        }
      })
      .catch(() => {});
  }, []);

  useEffect(() => {
    setQuantities(Object.fromEntries(allFragrances.map(f => [f.name, 1])));
  }, [allFragrances]);

  const filteredFragrances = allFragrances.filter(f => f.category === selectedCategory);

  const [emblaRef, emblaApi] = useEmblaCarousel(
    { loop: true, align: 'center', skipSnaps: false, containScroll: false },
    [Autoplay({ delay: 3000, stopOnInteraction: false, stopOnMouseEnter: true })]
  );

  const onSelect = useCallback(() => {
    if (!emblaApi) return;
    setSelectedIndex(emblaApi.selectedScrollSnap());
    setExpandedId(null);
  }, [emblaApi]);

  useEffect(() => {
    if (!emblaApi) return;
    onSelect();
    emblaApi.on('select', onSelect);
    emblaApi.on('reInit', onSelect);
  }, [emblaApi, onSelect]);

  useEffect(() => {
    if (emblaApi) {
      setTimeout(() => {
        emblaApi.reInit();
        emblaApi.scrollTo(0, false);
        setSelectedIndex(0);
      }, 50);
    }
  }, [selectedCategory, emblaApi]);

  const stopAutoplay = useCallback(() => {
    emblaApi?.plugins()?.autoplay?.stop();
  }, [emblaApi]);

  const playAutoplay = useCallback(() => {
    emblaApi?.plugins()?.autoplay?.play();
  }, [emblaApi]);

  const updateQty = (name: string, delta: number) => {
    setQuantities(prev => ({ ...prev, [name]: Math.max(1, (prev[name] || 1) + delta) }));
  };

  const handleAddToCart = (e: React.MouseEvent, product: Fragrance) => {
    e.preventDefault();
    e.stopPropagation();
    const savedCart = localStorage.getItem('cart');
    let cart = savedCart ? JSON.parse(savedCart) : [];
    const qty = quantities[product.name] || 1;
    const idx = cart.findIndex((i: any) => i.name === product.name);
    if (idx !== -1) {
      cart[idx].qty = Number(cart[idx].qty) + qty;
    } else {
      cart.push({ name: product.name, image: product.image, price: product.price || 4200, qty, category: 'Collection', size: '100ml EDP' });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    window.dispatchEvent(new Event('cartUpdated'));
    window.dispatchEvent(new Event('storage'));
    setExpandedId(null);
    playAutoplay();
  };

  return (
    <section className="relative py-12 md:py-32 px-4 md:px-20 bg-[#F9F6F1] dark:bg-zinc-950 overflow-hidden">
      <div className="max-w-7xl mx-auto flex flex-col md:flex-row gap-6 items-start">

        {/* Sidebar */}
        <div className="w-full md:w-1/4 flex flex-col z-10 md:pt-16 h-full relative">
          <h3 className="text-[13px] tracking-[0.4em] uppercase text-gray-600 mb-4 font-semibold text-center md:text-left font-qlassy">
            Explore Categories
          </h3>
          <div className="md:pt-20">
            <ul className="flex md:flex-col justify-center gap-4 md:gap-4 w-full">
              {categories.map((cat, index) => (
                <li key={cat.name} className="relative py-1">
                  <button
                    onClick={() => setSelectedCategory(cat.name)}
                    className={`text-xl sm:text-2xl md:text-[1.8rem] transition-all duration-500 relative flex flex-col items-center md:items-start font-qlassy tracking-tight ${
                      selectedCategory === cat.name ? 'text-black md:translate-x-1' : 'text-gray-300'
                    }`}
                  >
                    {cat.label}
                    <span 
                      className={`h-[1.5px] bg-black mt-1 transition-all duration-500 absolute -bottom-1 
                        ${selectedCategory === cat.name ? 'w-5' : 'w-0'}
                        ${index === 0 ? 'left-0' : ''} 
                        ${index === 1 ? 'left-1/2 -translate-x-1/2' : ''}
                        ${index === 2 ? 'right-0' : ''}
                      `} 
                    />
                  </button>
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Slider */}
        <div className="w-full md:w-3/4 relative group z-10">
          <div className="absolute top-[40%] -translate-y-1/2 left-0 right-0 z-40 pointer-events-none flex justify-center">
            <div className="w-full max-w-[290px] sm:max-w-[480px] md:max-w-[340px] lg:max-w-[420px] xl:max-w-[460px] flex justify-between items-center px-2">
              <button onClick={() => emblaApi?.scrollPrev()} className="w-8 h-8 md:w-12 md:h-12 rounded-full bg-[#D5C6B3] shadow-lg flex items-center justify-center pointer-events-auto transition-all duration-300 opacity-100 md:opacity-0 md:group-hover:opacity-100 active:scale-90">
                <ChevronLeft size={20} className="text-white md:w-6 md:h-6" />
              </button>
              <button onClick={() => emblaApi?.scrollNext()} className="w-8 h-8 md:w-12 md:h-12 rounded-full bg-[#D5C6B3] shadow-lg flex items-center justify-center pointer-events-auto transition-all duration-300 opacity-100 md:opacity-0 md:group-hover:opacity-100 active:scale-90">
                <ChevronRight size={20} className="text-white md:w-6 md:h-6" />
              </button>
            </div>
          </div>

          <AnimatePresence mode="wait">
            <motion.div key={selectedCategory} initial={{ opacity: 0, scale: 0.98 }} animate={{ opacity: 1, scale: 1 }} exit={{ opacity: 0, scale: 1.02 }} transition={{ duration: 0.4, ease: "easeInOut" }} ref={emblaRef} className="overflow-hidden py-6 md:py-10">
              <div className="flex">
                {filteredFragrances.map((item, idx) => {
                  const isActive   = idx === selectedIndex;
                  const isExpanded = expandedId === item.name;
                  return (
                    <div key={`${selectedCategory}-${idx}`} className="flex-[0_0_75%] sm:flex-[0_0_50%] lg:flex-[0_0_33.33%] min-w-0 px-3 md:px-6" onMouseEnter={stopAutoplay} onMouseLeave={isExpanded ? undefined : playAutoplay}>
                      <div className="transition-all duration-700 ease-out relative" style={{ transform: isActive ? (typeof window !== 'undefined' && window.innerWidth < 768 ? 'scale(0.95)' : 'scale(1.15)') : 'scale(0.75)', opacity: isActive ? 1 : 0.4, zIndex: isActive ? 10 : 1 }}>
                        <Link href={item.href} className="block cursor-pointer">
                          <FragranceCard {...item} />
                        </Link>

                        <div className={`absolute bottom-4 right-0 flex items-center transition-all duration-500 ${isActive ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-4 pointer-events-none'}`}>
                          <div className="flex items-center gap-0 bg-white dark:bg-zinc-900 rounded-full shadow-xl border border-black/5 overflow-hidden">
                            <AnimatePresence>
                              {isExpanded && (
                                <motion.div initial={{ width: 0, opacity: 0 }} animate={{ width: "auto", opacity: 1 }} exit={{ width: 0, opacity: 0 }} className="flex items-center px-1">
                                  <button onClick={(e) => { e.preventDefault(); e.stopPropagation(); updateQty(item.name, -1); }} className="w-6 h-6 flex items-center justify-center hover:bg-black/5 rounded-full transition-colors"><Minus size={10} /></button>
                                  <span className="w-6 text-center text-[10px] font-bold font-glacial tabular-nums">{quantities[item.name]}</span>
                                  <button onClick={(e) => { e.preventDefault(); e.stopPropagation(); updateQty(item.name, 1); }} className="w-6 h-6 flex items-center justify-center hover:bg-black/5 rounded-full transition-colors"><Plus size={10} /></button>
                                  <button className="ml-1 bg-black text-white text-[8px] px-2 py-1 rounded-full font-bold uppercase tracking-wider active:scale-95 font-glacial" onClick={(e) => handleAddToCart(e, item)}>Add</button>
                                </motion.div>
                              )}
                            </AnimatePresence>
                            <button
                              className={`w-8 h-8 flex items-center justify-center transition-all duration-300 ${isExpanded ? 'bg-transparent text-black' : 'bg-black text-white'}`}
                              onClick={(e) => { e.preventDefault(); e.stopPropagation(); setExpandedId(isExpanded ? null : item.name); }}
                            >
                              {isExpanded ? <X size={12} strokeWidth={2.5} /> : <ShoppingBag size={12} />}
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            </motion.div>
          </AnimatePresence>

          <div className="absolute inset-y-0 left-0 w-12 md:w-24 bg-gradient-to-r from-[#F9F6F1] to-transparent z-10 pointer-events-none hidden md:block" />
          <div className="absolute inset-y-0 right-0 w-12 md:w-24 bg-gradient-to-l from-[#F9F6F1] to-transparent z-10 pointer-events-none hidden md:block" />
        </div>
      </div>
    </section>
  );
}