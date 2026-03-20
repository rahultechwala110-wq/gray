'use client';

import { Star, ShoppingBag, CreditCard, ArrowUpRight } from 'lucide-react';
import { motion, Variants } from 'framer-motion';
import { useState, useEffect, useRef } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';

interface ShowcaseSettings {
  label: string;
  title: string;
  description: string;
  btn_text: string;
  btn_link: string;
}

interface ShowcaseProduct {
  id?: number;
  name: string;
  image: string;
  price: number;
  price_range: string;
  rating: number;
  href: string;
}

const fallbackSettings: ShowcaseSettings = {
  label: 'Shop Products',
  title: 'Our Collections',
  description: 'Experience the art of premium craftsmanship',
  btn_text: 'Shop All Collections',
  btn_link: '/all-products',
};

const fallbackProducts: ShowcaseProduct[] = [
  { name: 'Gentle',    image: '/products/product-1.png', price: 4200, price_range: '$35 - $70', rating: 5, href: '/product' },
  { name: 'Brilliance',image: '/products/product-2.png', price: 3800, price_range: '$40 - $70', rating: 5, href: '/product' },
  { name: 'Groomed',   image: '/products/product-3.png', price: 2200, price_range: '$30 - $70', rating: 4, href: '/product' },
  { name: 'Bliss',     image: '/products/product-4.png', price: 3500, price_range: '$30 - $50', rating: 5, href: '/product' },
  { name: 'Boss',      image: '/products/product-5.png', price: 4500, price_range: '$35 - $55', rating: 5, href: '/product' },
  { name: 'Gorgeous',  image: '/products/product-6.png', price: 3200, price_range: '$35 - $70', rating: 5, href: '/product' },
  { name: 'Bold',      image: '/products/product-7.png', price: 4800, price_range: '$40 - $60', rating: 5, href: '/product' },
  { name: 'Braveheart',image: '/products/product-8.png', price: 5200, price_range: '$40 - $60', rating: 5, href: '/product' },
];

const containerVariants: Variants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { staggerChildren: 0.1 } }
};
const itemVariants: Variants = {
  hidden: { opacity: 0, y: 30 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.6, ease: "easeOut" } }
};

export default function ProductsShowcase() {
  const router = useRouter();
  const [settings, setSettings]   = useState<ShowcaseSettings>(fallbackSettings);
  const [products, setProducts]   = useState<ShowcaseProduct[]>(fallbackProducts);
  const [activeProduct, setActiveProduct] = useState<number | null>(null);
  const [quantityOpenIndex, setQuantityOpenIndex] = useState<number | null>(null);
  const [quantities, setQuantities] = useState<{ [key: number]: number }>({});
  const scrollRef = useRef<HTMLDivElement>(null);
  const [isPaused, setIsPaused] = useState(false);

  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/showcase.php`, { cache: 'no-store' })
      .then(r => r.json())
      .then(({ settings: s, products: p }) => {
        if (s) setSettings(s);
        if (p?.length) {
          setProducts(p.slice(0, 8).map((item: ShowcaseProduct) => ({
            ...item,
            image: item.image || '',
          })));
        }
      })
      .catch(() => {});
  }, []);

  const getQty = (i: number) => quantities[i] ?? 1;
  const incrementQty = (i: number) => setQuantities(prev => ({ ...prev, [i]: (prev[i] ?? 1) + 1 }));
  const decrementQty = (i: number) => setQuantities(prev => ({ ...prev, [i]: Math.max(1, (prev[i] ?? 1) - 1) }));

  const handleAddToCartClick = (e: React.MouseEvent, index: number) => {
    e.stopPropagation();
    setQuantityOpenIndex(index);
    setQuantities(prev => ({ ...prev, [index]: 1 }));
  };

  const updateCartStorage = (product: ShowcaseProduct, qty: number) => {
    const savedCart = localStorage.getItem('cart');
    let cart: any[] = [];
    try { cart = savedCart ? JSON.parse(savedCart) : []; } catch { cart = []; }
    const idx = cart.findIndex((i: any) => i.name === product.name);
    if (idx !== -1) {
      cart[idx].qty = Number(cart[idx].qty || 0) + qty;
    } else {
      cart.push({ name: product.name, image: product.image, price: product.price || 4200, qty, category: 'Collection', size: '100ml EDP' });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    window.dispatchEvent(new Event('cartUpdated'));
    window.dispatchEvent(new Event('storage'));
  };

  const handleConfirmAdd = (e: React.MouseEvent, product: ShowcaseProduct, index: number) => {
    e.stopPropagation();
    updateCartStorage(product, getQty(index));
    setQuantityOpenIndex(null);
  };

  const handleBuyNow = (e: React.MouseEvent, product: ShowcaseProduct) => {
    e.stopPropagation();
    localStorage.setItem('checkout_now', JSON.stringify([{
      name: product.name, image: product.image, price: product.price || 4200,
      qty: 1, category: 'Collection', size: '100ml EDP', isDirectCheckout: true
    }]));
    router.push('/check-out');
  };

  useEffect(() => {
    const interval = setInterval(() => {
      if (scrollRef.current && !isPaused && window.innerWidth < 1024) {
        const { scrollLeft, scrollWidth, clientWidth } = scrollRef.current;
        if (scrollLeft + clientWidth >= scrollWidth - 10) {
          scrollRef.current.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
          scrollRef.current.scrollBy({ left: window.innerWidth < 640 ? clientWidth : clientWidth / 2, behavior: 'smooth' });
        }
      }
    }, 3000);
    return () => clearInterval(interval);
  }, [isPaused]);

  return (
    <section
      className="pt-6 pb-12 md:py-16 bg-[#F9F6F1] overflow-hidden"
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
      onTouchStart={() => setIsPaused(true)}
    >
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        className="max-w-7xl mx-auto text-center mb-16 md:mb-28 px-6 space-y-3"
      >
        <span className="text-[13px] tracking-[0.4em] uppercase font-bold text-gray-600 block mb-4 font-qlassy">
          {settings.label}
        </span>
        <h2 className="text-4xl md:text-5xl font-qlassy text-[#1A1A1A] mb-3 tracking-wide leading-none">
          {settings.title}
        </h2>
        <p className="text-[16px] md:text-[18px] text-gray-700 tracking-[0.04em] leading-relaxed font-glacial font-medium">
          {settings.description}
        </p>
      </motion.div>

      <motion.div
        ref={scrollRef}
        variants={containerVariants}
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, margin: "-100px" }}
        className="max-w-7xl mx-auto flex flex-row overflow-x-auto snap-x snap-mandatory pb-10
                   lg:grid lg:grid-cols-4 lg:gap-8 lg:gap-y-20 lg:px-20 lg:overflow-visible no-scrollbar"
      >
        {products.map((product, index) => {
          const isActive  = activeProduct === index;
          const isBig     = index % 2 === 0;
          const isQtyOpen = quantityOpenIndex === index;
          const qty       = getQty(index);

          return (
            <motion.div
              key={product.id ?? index}
              variants={itemVariants}
              onMouseEnter={() => setActiveProduct(index)}
              onMouseLeave={() => setActiveProduct(null)}
              className="flex-shrink-0 w-full sm:w-1/2 lg:w-full snap-center flex flex-col items-center group cursor-pointer px-4 lg:px-0"
            >
              <div className="w-full flex flex-col items-center relative">
                <Link href={product.href} className="w-full">
                  <div className="relative w-full aspect-square flex items-end justify-center mb-6 bg-[#F9F6F1] overflow-hidden">
                    <div className={`relative transition-all duration-1000 ease-[cubic-bezier(0.23,1,0.32,1)] transform ${isBig ? 'w-[80%] md:w-[75%] h-[90%]' : 'w-[60%] md:w-[55%] h-[70%]'} ${isActive ? '-translate-y-1.5' : 'translate-y-0'}`}>
                      <img
                        src={product.image}
                        alt={product.name}
                        className="absolute inset-0 w-full h-full object-contain object-bottom drop-shadow-[0_10px_20px_rgba(0,0,0,0.05)] transition-all duration-700 md:group-hover:drop-shadow-[0_15px_30px_rgba(0,0,0,0.1)]"
                      />
                    </div>
                  </div>
                </Link>

                <div className="text-center space-y-1 md:space-y-2 w-full px-4 font-qlassy">
                  <h3 className="text-[13px] md:text-[14px] font-bold uppercase tracking-[0.15em] md:tracking-[0.2em] text-gray-800 transition-colors duration-500 group-hover:text-black leading-tight">
                    {product.name}
                  </h3>
                  <div className="flex justify-center items-center">
                    <div className="flex items-center gap-1.5 border border-black/5 text-[#00002e] text-[12px] md:text-[14px] px-2 py-1 rounded-full">
                      <span className="font-bold">{Number(product.rating).toFixed(1)}</span>
                      <Star size={12} fill="currentColor" />
                    </div>
                  </div>
                  <p className="text-[14px] md:text-base font-bold tracking-tight text-black/80">
                    {product.price_range || `₹${product.price}`}
                  </p>
                </div>
              </div>

              <div className="w-full px-2 mt-4 flex justify-center">
                <div className={`flex flex-row md:flex-col gap-1.5 md:gap-2 w-full max-w-[320px] md:max-w-[180px] transition-all duration-700 ease-out ${isActive ? 'md:max-h-40 md:opacity-100 md:pt-6' : 'opacity-100 md:max-h-0 md:opacity-0 md:pt-0'}`}>
                  {isQtyOpen ? (
                    <motion.div
                      initial={{ opacity: 0, y: 6, scale: 0.96 }}
                      animate={{ opacity: 1, y: 0, scale: 1 }}
                      transition={{ duration: 0.18, ease: 'easeOut' }}
                      className="flex items-center gap-1 bg-white border border-black/10 rounded-full px-2 py-1.5 shadow-md font-glacial w-full justify-center"
                      onClick={(e) => e.stopPropagation()}
                    >
                      <button onClick={(e) => { e.stopPropagation(); decrementQty(index); }} className="w-6 h-6 flex items-center justify-center text-black/50 hover:text-black text-xl leading-none font-light transition-colors">−</button>
                      <span className="min-w-[22px] text-center text-[13px] font-semibold text-black select-none">{qty}</span>
                      <button onClick={(e) => { e.stopPropagation(); incrementQty(index); }} className="w-6 h-6 flex items-center justify-center text-black/50 hover:text-black text-xl leading-none font-light transition-colors">+</button>
                      <button onClick={(e) => handleConfirmAdd(e, product, index)} className="bg-black text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full transition-colors ml-1">ADD</button>
                      <button onClick={(e) => { e.stopPropagation(); setQuantityOpenIndex(null); }} className="w-6 h-6 flex items-center justify-center text-black/30 hover:text-black text-lg leading-none transition-colors ml-0.5">×</button>
                    </motion.div>
                  ) : (
                    <button onClick={(e) => handleAddToCartClick(e, index)} className="flex-1 md:w-full border border-black text-black text-[9px] sm:text-[11px] font-bold uppercase py-3 rounded-sm hover:bg-black hover:text-white transition-all tracking-widest flex items-center justify-center gap-1.5 sm:gap-2 font-glacial">
                      <ShoppingBag className="w-3.5 h-3.5 md:w-4 md:h-4" />
                      <span className="whitespace-nowrap">Add To Cart</span>
                    </button>
                  )}
                  <button onClick={(e) => handleBuyNow(e, product)} className="flex-1 md:w-full border border-black text-black text-[9px] sm:text-[11px] font-bold uppercase py-3 rounded-sm hover:bg-black hover:text-white transition-all tracking-widest flex items-center justify-center gap-1.5 sm:gap-2 font-glacial">
                    <CreditCard className="w-3.5 h-3.5 md:w-4 md:h-4" />
                    <span className="whitespace-nowrap">Buy Now</span>
                  </button>
                </div>
              </div>
            </motion.div>
          );
        })}
      </motion.div>

      <div className="text-center mt-6 md:mt-10">
        <Link href={settings.btn_link} className="inline-flex items-center gap-3 group mx-auto font-qlassy font-bold">
          <span className="text-[12px] tracking-[0.2em] uppercase text-gray-900 border-b border-black/20 pb-0.5 group-hover:border-black transition-colors duration-300">
            {settings.btn_text}
          </span>
          <ArrowUpRight size={14} strokeWidth={2} className="text-gray-400 group-hover:text-gray-900 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-300" />
        </Link>
      </div>

      <style jsx global>{`
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
      `}</style>
    </section>
  );
}