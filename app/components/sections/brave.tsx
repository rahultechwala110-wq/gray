'use client';

import { useState, useRef, useEffect, useCallback } from 'react';
import { Star, ShoppingBag, CreditCard, MapPin, CheckCircle2, XCircle, Loader2 } from 'lucide-react';
import Icon from '../ui/Icon';
import { motion, Variants } from 'framer-motion';
import useEmblaCarousel from 'embla-carousel-react';
import Autoplay from 'embla-carousel-autoplay';
import Link from 'next/link';
import { useRouter, usePathname } from 'next/navigation';

// ─── Types ────────────────────────────────────────────────────────────────────

interface ProductData {
  id: number;
  slug: string;
  name: string;
  subtitle: string;
  price: number;
  description: string;
  full_description: string;
  volume: string;
  key_notes: string[];
  ingredients: string;
  caution: string;
  best_before: string;
  image1: string;
  image2: string;
  image3: string;
  video1: string;
  video2: string;
  whisper1_image: string;
  whisper1_heading: string;
  whisper1_content: string;
  whisper2_heading: string;
  whisper2_content: string;
  made_with_love: string;
  made_subtitle: string;
}

interface ShowcaseProduct {
  id: number;
  name: string;
  image: string;
  price: number;
  price_range: string;
  rating: number;
  href: string;
}

interface ShowcaseSettings {
  label: string;
  title: string;
  description: string;
  btn_text: string;
  btn_link: string;
}

// ─── Default Product ──────────────────────────────────────────────────────────

const defaultProduct: ProductData = {
  id: 0, slug: '',
  name: 'BRAVE',
  subtitle: 'Extrait de Parfume',
  price: 285,
  description: 'A rich embrace of amber, vanilla and honey layered over woody warmth.',
  full_description: `BRAVE by GRAY is crafted for the man who moves with quiet confidence and unmistakable presence. This fragrance does not announce itself loudly — it commands attention through depth, warmth, and control.

Opening with a refined brightness, BRAVE evolves into a powerful heart of woody and amber accords, balanced by subtle floral softness that adds elegance without dilution. A gentle layer of honeyed warmth and nutty undertones enrich the composition, before settling into a smooth vanilla base that lingers with composure and strength.

Formulated with a 25%+ perfume oil concentration, BRAVE delivers exceptional performance, offering 24 hours or more longevity on fabric with a moderate projection designed to be felt, not forced.

Created for after‑dark moments, BRAVE is ideal for dinners, dates, and late evenings where restraint speaks louder than excess. It is a fragrance for men who prefer identity over trends, and substance over noise.

Encased in GRAY's signature collectible bottle and presented in premium gift‑ready packaging, BRAVE transforms fragrance into a considered ritual — refined, intentional, and enduring.

BRAVE is not worn casually. It is chosen.`,
  volume: '55 ml',
  key_notes: ['Woody', 'Amber', 'Floral', 'Honey', 'Vanilla', 'Nutty'],
  ingredients: 'Perfume Fragrance Oil 25%, Alcohol Denat, Aqua, Fixatives',
  caution: 'Flammable, Keep away from heat & fire. Keep out of reach of children. Harmful if consumed internally. Do not spray near eyes or face. Handle with elegance, preserve with care: keep the bottle nestled in its box to ensure its beauty endures.',
  best_before: '36 Months from Mfg. Date',
  image1: '/productpage/1.png',
  image2: '/productpage/two.png',
  image3: '/productpage/3.png',
  video1: '/productpage/video.mp4',
  video2: '/productpage/video-2.mp4',
  whisper1_image: '/productpage/wood.jpg',
  whisper1_heading: 'First Whisper',
  whisper1_content: 'Opening with a refined brightness, BRAVE evolves into a powerful heart of woody and amber accords, balanced by subtle floral softness that adds elegance without dilution.',
  whisper2_heading: 'Second Whisper',
  whisper2_content: 'A gentle layer of honeyed warmth and nutty undertones enrich the composition, before settling into a smooth vanilla base that lingers with composure and strength.',
  made_with_love: 'Made with love',
  made_subtitle: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
};

// ─── Pincode Checker ──────────────────────────────────────────────────────────

const PINCODE_DB: Record<string, { days: string; city: string }> = {
  '110001': { days: '1–2 business days', city: 'New Delhi' },
  '400001': { days: '2–3 business days', city: 'Mumbai' },
  '700001': { days: '3–4 business days', city: 'Kolkata' },
  '600001': { days: '2–3 business days', city: 'Chennai' },
  '560001': { days: '2–3 business days', city: 'Bangalore' },
  '500001': { days: '3–4 business days', city: 'Hyderabad' },
};

type DeliveryResult =
  | { status: 'available'; days: string; city: string }
  | { status: 'unavailable' }
  | null;

function PincodeChecker() {
  const [pincode, setPincode] = useState('');
  const [loading, setLoading] = useState(false);
  const [result, setResult]   = useState<DeliveryResult>(null);

  const handleCheck = () => {
    if (pincode.length !== 6 || !/^\d{6}$/.test(pincode)) return;
    setLoading(true); setResult(null);
    setTimeout(() => {
      const found = PINCODE_DB[pincode];
      setResult(found ? { status: 'available', days: found.days, city: found.city } : { status: 'unavailable' });
      setLoading(false);
    }, 800);
  };

  return (
    <div className="mb-6 pb-6 border-b border-gray-100">
      <div className="flex items-center gap-1.5 mb-3">
        <MapPin size={12} className="text-gray-400" strokeWidth={1.5} />
        <span className="text-[10px] tracking-[0.3em] uppercase text-gray-400 font-qlassy">Check Delivery</span>
      </div>
      <div className="flex items-stretch border border-gray-200 rounded-sm overflow-hidden focus-within:border-gray-400 transition-colors duration-200">
        <input type="text" inputMode="numeric" maxLength={6} value={pincode}
          onChange={(e) => { setPincode(e.target.value.replace(/\D/g, '')); setResult(null); }}
          onKeyDown={(e) => e.key === 'Enter' && handleCheck()}
          placeholder="Enter pincode"
          className="flex-1 bg-transparent text-[12px] tracking-[0.1em] px-3 py-2.5 text-gray-800 placeholder:text-gray-300 outline-none font-qlassy" />
        <button onClick={handleCheck} disabled={pincode.length !== 6 || loading}
          className="relative overflow-hidden px-4 py-2.5 bg-black text-white text-[9px] tracking-[0.3em] uppercase font-qlassy disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center whitespace-nowrap group">
          <span className="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-out" style={{ background: '#2e2e2e' }} />
          <span className="relative z-10 flex items-center gap-1.5">{loading ? <Loader2 size={11} className="animate-spin" /> : "Check"}</span>
        </button>
      </div>
      {result && (
        <div className={`mt-3 flex items-start gap-2 text-[11px] font-glacial tracking-[0.08em] leading-relaxed ${result.status === 'available' ? 'text-emerald-700' : 'text-red-400'}`}>
          {result.status === 'available' ? (
            <><CheckCircle2 size={13} className="mt-0.5 flex-shrink-0" strokeWidth={1.8} /><span>Delivery to <strong>{result.city}</strong> in <strong>{result.days}</strong>.</span></>
          ) : (
            <><XCircle size={13} className="mt-0.5 flex-shrink-0" strokeWidth={1.8} /><span>Delivery not available at this pincode.</span></>
          )}
        </div>
      )}
    </div>
  );
}

// ─── Product Carousel ─────────────────────────────────────────────────────────

function ProductCarousel({ images, activeThumb, setActiveThumb }: {
  images: string[]; activeThumb: number; setActiveThumb: (i: number) => void;
}) {
  const [emblaRef, emblaApi] = useEmblaCarousel(
    { loop: true, dragFree: false },
    [Autoplay({ delay: 4000, stopOnInteraction: false })]
  );

  useEffect(() => { if (emblaApi) emblaApi.scrollTo(activeThumb); }, [emblaApi, activeThumb]);

  const onSelect = useCallback(() => {
    if (!emblaApi) return;
    setActiveThumb(emblaApi.selectedScrollSnap());
  }, [emblaApi, setActiveThumb]);

  useEffect(() => {
    if (!emblaApi) return;
    emblaApi.on('select', onSelect);
  }, [emblaApi, onSelect]);

  return (
    <div className="relative flex flex-col select-none">
      <div className="overflow-hidden w-full aspect-[3/4] md:aspect-auto md:h-[620px] -mt-4 md:-mt-12" ref={emblaRef}>
        <div className="flex h-full">
          {images.map((src, i) => (
            <div key={i} className="relative flex-[0_0_100%] min-w-0 h-full">
              <img src={src} alt={`product-${i}`}
                className="absolute inset-0 w-full h-full object-contain object-bottom pointer-events-none"
                draggable={false} />
            </div>
          ))}
        </div>
      </div>
      <div className="flex items-center gap-2 mt-5">
        {images.map((_, i) => (
          <button key={i} onClick={() => { setActiveThumb(i); emblaApi?.scrollTo(i); }}
            className={`rounded-full transition-all duration-300 ${activeThumb === i ? 'bg-black w-6 h-2' : 'bg-gray-300 w-2 h-2 hover:bg-gray-500'}`} />
        ))}
        <span className="ml-auto text-[11px] text-gray-400 tracking-widest uppercase">{activeThumb + 1} / {images.length}</span>
      </div>
      <div className="flex gap-3 mt-3">
        {images.map((img, i) => (
          <button key={i} onClick={() => { setActiveThumb(i); emblaApi?.scrollTo(i); }}
            className={`relative w-14 h-14 border-2 transition-all ${activeThumb === i ? 'border-black' : 'border-transparent opacity-50'}`}>
            <img src={img} alt={`thumb-${i}`} className="absolute inset-0 w-full h-full object-contain" draggable={false} />
          </button>
        ))}
      </div>
    </div>
  );
}

// ─── Tab Data ─────────────────────────────────────────────────────────────────

const tabs = ['Description', 'Ingredients', 'Review'];
const reviews = [
  { name: 'Robert Dunn', stars: 5, text: 'A fresh and verdant fragrance, with bright citrus, peppery green spice, a herbaceous heart and warm woods—evoking sunny afternoons sipping tea beneath an arbour of fig trees.' },
  { name: 'Robert Dunn', stars: 5, text: 'A fresh and verdant fragrance, with bright citrus, peppery green spice, a herbaceous heart and warm woods—evoking sunny afternoons sipping tea beneath an arbour of fig trees.' },
  { name: 'Robert Dunn', stars: 5, text: 'A fresh and verdant fragrance, with bright citrus, peppery green spice, a herbaceous heart and warm woods—evoking sunny afternoons sipping tea beneath an arbour of fig trees.' },
  { name: 'Robert Dunn', stars: 5, text: 'A fresh and verdant fragrance, with bright citrus, peppery green spice, a herbaceous heart and warm woods—evoking sunny afternoons sipping tea beneath an arbour of fig trees.' },
];

// ─── Main ProductPage ─────────────────────────────────────────────────────────

export default function ProductPage() {
  const pathname = usePathname();
  const router   = useRouter();
  const [product, setProduct]                         = useState<ProductData>(defaultProduct);
  const [pageLoading, setPageLoading]                 = useState(true);
  const [activeTab, setActiveTab]                     = useState('Description');
  const [activeThumb, setActiveThumb]                 = useState(0);
  const [isSecondMuted, setIsSecondMuted]             = useState(true);
  const [isMadeWithLoveMuted, setIsMadeWithLoveMuted] = useState(true);
  const secondVideoRef  = useRef<HTMLVideoElement>(null);
  const madeWithLoveRef = useRef<HTMLVideoElement>(null);
  const [hoveredBtn, setHoveredBtn] = useState<'cart' | 'buy' | null>(null);
  const [topQtyOpen, setTopQtyOpen] = useState(false);
  const [topQty, setTopQty]         = useState(1);

  useEffect(() => {
    // ✅ Extract last segment as slug: /product/brave → "brave"
    const segments = pathname.replace(/^\/|\/$/g, '').split('/');
    const slug = segments[segments.length - 1];
    if (!slug) { setPageLoading(false); return; }

    setPageLoading(true);
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/product.php?slug=${encodeURIComponent(slug)}`, { cache: 'no-store' })
      .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
      .then(d => { if (d) setProduct(d); })
      .catch(err => console.error('Failed to fetch product:', err))
      .finally(() => setPageLoading(false));
  }, [pathname]);

  const toggleSecondMute = () => {
    if (secondVideoRef.current) { secondVideoRef.current.muted = !secondVideoRef.current.muted; setIsSecondMuted(secondVideoRef.current.muted); }
  };
  const toggleMadeWithLoveMute = () => {
    if (madeWithLoveRef.current) { madeWithLoveRef.current.muted = !madeWithLoveRef.current.muted; setIsMadeWithLoveMuted(madeWithLoveRef.current.muted); }
  };

  const handleTopAddToCart = () => {
    const savedCart = localStorage.getItem('cart');
    let cart: any[] = [];
    try { cart = savedCart ? JSON.parse(savedCart) : []; } catch { cart = []; }
    const idx = cart.findIndex((i: any) => i.name === product.name);
    if (idx !== -1) { cart[idx].qty = Number(cart[idx].qty || 0) + topQty; }
    else { cart.push({ name: product.name, image: product.image1, price: product.price, qty: topQty, category: 'Collection', size: product.volume }); }
    localStorage.setItem('cart', JSON.stringify(cart));
    window.dispatchEvent(new Event('cartUpdated'));
    window.dispatchEvent(new Event('storage'));
    setTopQtyOpen(false); setTopQty(1);
  };

  const handleTopBuyNow = () => {
    localStorage.setItem('checkout_now', JSON.stringify([{
      name: product.name, image: product.image1, price: product.price,
      qty: 1, category: 'Collection', size: product.volume
    }]));
    router.push('/check-out');
  };

  const images = [product.image1, product.image2, product.image3].filter(Boolean);

  if (pageLoading) {
    return (
      <main className="bg-[#F9F6F1] min-h-screen">
        <section className="max-w-7xl mx-auto px-4 sm:px-6 pt-32 md:pt-36 pb-12 grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-16">
          <div className="w-full aspect-[3/4] md:h-[620px] bg-gray-200 animate-pulse rounded-sm" />
          <div className="flex items-center">
            <div className="bg-white p-10 shadow-sm w-full space-y-4">
              <div className="h-8 bg-gray-200 animate-pulse rounded w-1/3" />
              <div className="h-4 bg-gray-200 animate-pulse rounded w-1/4" />
              <div className="h-6 bg-gray-200 animate-pulse rounded w-1/5" />
              <div className="h-20 bg-gray-200 animate-pulse rounded w-full" />
              <div className="h-12 bg-gray-200 animate-pulse rounded w-full" />
              <div className="h-12 bg-gray-200 animate-pulse rounded w-full" />
            </div>
          </div>
        </section>
      </main>
    );
  }

  return (
    <main className="bg-[#F9F6F1] min-h-screen">

      {/* ── Hero: Carousel + Info ── */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 pt-32 md:pt-36 pb-12 grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-16 items-stretch">
        <ProductCarousel images={images} activeThumb={activeThumb} setActiveThumb={setActiveThumb} />
        <div className="flex items-center">
          <div className="bg-white p-6 sm:p-8 md:p-10 shadow-sm w-full">
            <h1 className="text-2xl md:text-3xl text-gray-900 mb-1 font-qlassy tracking-tight">{product.name}</h1>
            <p className="text-[11px] tracking-[0.25em] uppercase text-gray-400 font-qlassy mb-3">{product.subtitle}</p>
            <p className="text-xl md:text-2xl font-glacial text-gray-800 mb-5 font-light">₹{product.price}.00</p>
            <p className="text-[14px] sm:text-[15px] font-glacial text-gray-600 leading-relaxed mb-8 max-w-md">{product.description}</p>
            <div className="mb-8">
              <span className="text-[11px] sm:text-[12px] tracking-widest uppercase text-gray-400 border-b border-gray-100 pb-1">Size: {product.volume}</span>
            </div>
            <PincodeChecker />
            <div className="flex flex-col sm:flex-row gap-3">
              {topQtyOpen ? (
                <motion.div initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }} transition={{ duration: 0.15, ease: 'easeOut' }}
                  className="flex-1 flex items-center justify-center gap-0.5 bg-white border border-black/15 rounded-sm px-2 py-3 sm:py-0 shadow-sm font-glacial min-w-0">
                  <button onClick={() => setTopQty(q => Math.max(1, q - 1))} className="w-8 h-8 sm:w-6 sm:h-6 flex-shrink-0 flex items-center justify-center text-black/50 hover:text-black text-lg leading-none font-light transition-colors">−</button>
                  <span className="w-8 sm:w-6 text-center text-[13px] font-semibold text-black select-none flex-shrink-0">{topQty}</span>
                  <button onClick={() => setTopQty(q => q + 1)} className="w-8 h-8 sm:w-6 sm:h-6 flex-shrink-0 flex items-center justify-center text-black/50 hover:text-black text-lg leading-none font-light transition-colors">+</button>
                  <button onClick={handleTopAddToCart} className="bg-black text-white text-[10px] font-bold uppercase tracking-widest px-4 py-2 rounded-full ml-1 flex-shrink-0">ADD</button>
                  <button onClick={() => { setTopQtyOpen(false); setTopQty(1); }} className="w-8 h-8 sm:w-6 sm:h-6 flex-shrink-0 flex items-center justify-center text-black/30 hover:text-black text-xl sm:text-base leading-none transition-colors ml-1">×</button>
                </motion.div>
              ) : (
                <button onClick={() => setTopQtyOpen(true)} onMouseEnter={() => setHoveredBtn('cart')} onMouseLeave={() => setHoveredBtn(null)}
                  className={`flex-1 py-4 text-[12px] tracking-[0.2em] uppercase font-glacial border border-black transition-all duration-300 flex items-center justify-center gap-2 ${hoveredBtn === 'cart' ? 'bg-black text-white' : 'bg-transparent text-black'}`}>
                  <ShoppingBag size={13} strokeWidth={1.5} /><span>Add to cart</span>
                </button>
              )}
              <button onClick={handleTopBuyNow} onMouseEnter={() => setHoveredBtn('buy')} onMouseLeave={() => setHoveredBtn(null)}
                className={`flex-1 min-w-[120px] py-4 text-[12px] tracking-[0.2em] font-glacial uppercase border border-black transition-all duration-300 flex items-center justify-center gap-2 ${hoveredBtn === 'buy' ? 'bg-black text-white' : 'bg-transparent text-black'}`}>
                <CreditCard size={13} strokeWidth={1.5} /><span className="whitespace-nowrap">Buy now</span>
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* ── Tabs ── */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 mt-6 md:mt-10">
        <div className="flex justify-start sm:justify-around border-b border-gray-200 relative overflow-x-auto gap-8 sm:gap-4 pb-1 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
          {tabs.map((tab) => (
            <button key={tab} onClick={() => setActiveTab(tab)}
              className={`text-[18px] md:text-[24px] font-qlassy pb-3 md:pb-4 whitespace-nowrap relative transition-all duration-300 ${activeTab === tab ? 'text-black opacity-100' : 'text-gray-400 opacity-60 hover:opacity-100'}`}>
              {tab}
              {activeTab === tab && <span className="absolute bottom-[-1px] left-0 w-full h-[2px] bg-black z-10 transition-all duration-300" />}
            </button>
          ))}
        </div>
      </section>

      {/* ── Tab Content ── */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 py-8 md:py-12 overflow-hidden">
        {activeTab === 'Review' && (
          <div className="w-full bg-white shadow-sm border border-gray-50 relative py-8 px-6 md:py-12 md:px-16 overflow-hidden h-auto animate-tab-content">
            <div className="absolute right-4 md:right-12 top-0 bottom-0 flex flex-col justify-around pointer-events-none opacity-5 md:opacity-10 select-none z-0">
              {[0,1,2].map((i) => (<span key={i} className="text-[100px] md:text-[140px] leading-none text-orange-900 font-qlassy">"</span>))}
            </div>
            <div className="relative z-10 space-y-12 md:space-y-16 max-h-[500px] md:max-h-[550px] overflow-y-auto pr-4 md:pr-10 custom-scrollbar">
              {reviews.map((r, i) => (
                <div key={i} className="max-w-full">
                  <h3 className="text-[16px] md:text-[18px] font-bold tracking-[0.15em] text-black uppercase mb-2 font-qlassy">{r.name}</h3>
                  <div className="flex gap-1 mb-3 md:mb-4">{[...Array(5)].map((_, s) => (<Star key={s} size={14} className="fill-amber-400 text-amber-400" />))}</div>
                  <p className="text-[16px] md:text-[19px] text-gray-700 leading-relaxed font-glacial">"{r.text}"</p>
                </div>
              ))}
            </div>
          </div>
        )}

        {activeTab === 'Description' && (
          <div className="w-full bg-white shadow-sm border border-gray-100 px-6 py-8 md:px-10 md:py-12 h-auto animate-tab-content">
            {product.full_description.split('\n\n').map((para, i) => (
              <p key={i} className="text-[16px] md:text-[19px] text-gray-700 leading-relaxed text-left font-glacial mb-5 last:mb-0">{para}</p>
            ))}
            <div className="mt-8 pt-6 border-t border-gray-100 space-y-3">
              <p className="text-[16px] md:text-[19px] text-gray-500 font-glacial leading-relaxed">
                <span className="font-semibold text-gray-700">Caution: </span>{product.caution}
              </p>
              <p className="text-[16px] md:text-[19px] text-gray-500 font-glacial leading-relaxed">
                <span className="font-semibold text-gray-700">Best Before: </span>{product.best_before}
              </p>
            </div>
          </div>
        )}

        {activeTab === 'Ingredients' && (
          <div className="w-full bg-white shadow-sm border border-gray-100 px-6 py-8 md:px-10 md:py-12 h-auto animate-tab-content">
            <h3 className="text-[13px] tracking-[0.25em] uppercase text-gray-400 font-qlassy mb-4">Key Notes</h3>
            <div className="flex flex-wrap gap-2 mb-8">
              {product.key_notes.map((note) => (
                <span key={note} className="px-4 py-1.5 border border-gray-200 text-[12px] tracking-[0.15em] uppercase font-glacial text-gray-700">{note}</span>
              ))}
            </div>
            <h3 className="text-[13px] tracking-[0.25em] uppercase text-gray-400 font-qlassy mb-3">Ingredients</h3>
            <p className="text-[16px] md:text-[19px] text-gray-700 leading-relaxed text-left font-glacial">{product.ingredients}</p>
          </div>
        )}

        <style jsx>{`
          @keyframes tabFadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
          .animate-tab-content { animation: tabFadeIn 0.4s ease-out forwards; }
          .custom-scrollbar { scrollbar-width: thin; scrollbar-color: #d6d3d1 transparent; }
        `}</style>
      </section>

      {/* ── Made with Love Video ── */}
      <section className="py-12 md:py-24 bg-[#F9F6F1] flex justify-center">
        <div className="relative w-[95%] max-w-[1450px] h-[350px] md:h-[520px] rounded-[30px] md:rounded-[40px] overflow-hidden shadow-xl bg-black">
          <video ref={madeWithLoveRef} autoPlay loop muted playsInline className="absolute inset-0 w-full h-full object-cover opacity-60">
            <source src={product.video1} type="video/mp4" />
          </video>
          <div className="absolute inset-0 flex flex-col justify-end p-6 md:p-16 pointer-events-none">
            <h2 className="text-4xl md:text-7xl text-white font-light tracking-tight mb-2 md:mb-4 leading-tight font-qlassy">{product.made_with_love}</h2>
            {product.made_subtitle && <p className="text-white/80 text-[14px] md:text-[16px] max-w-md leading-relaxed font-glacial">{product.made_subtitle}</p>}
          </div>
          <div className="absolute bottom-4 right-4 md:bottom-6 md:right-6 z-20">
            <button onClick={toggleMadeWithLoveMute} className="w-10 h-10 border border-white/20 rounded-full flex items-center justify-center bg-black/10 backdrop-blur-md">
              <Icon name={isMadeWithLoveMuted ? "volume_off" : "volume_up"} className="text-white text-lg" />
            </button>
          </div>
        </div>
      </section>

      {/* ── Whisper 1 ── */}
      <section className="bg-[#F9F6F1] w-full pt-6 md:pt-10 pb-0">
        <div className="flex flex-col md:grid md:grid-cols-2 md:min-h-[500px]">
          <div className="relative h-[300px] sm:h-[400px] md:h-auto bg-gray-200">
            <img src={product.whisper1_image || '/productpage/wood.jpg'} alt="Whisper 1"
              className="absolute inset-0 w-full h-full object-cover" />
          </div>
          <div className="bg-[#F9F6F1] flex flex-col justify-center px-6 md:px-20 py-12 md:py-10">
            <h3 className="text-2xl md:text-3xl text-gray-900 mb-3 font-qlassy tracking-tight">{product.whisper1_heading}</h3>
            <p className="text-[11px] md:text-[12px] tracking-[0.2em] uppercase font-glacial text-gray-800 mb-4 md:mb-5">
              Key notes: {product.key_notes.slice(0, 3).join(', ')}
            </p>
            <p className="text-[15px] md:text-[16px] text-gray-600 font-glacial leading-relaxed max-w-md">{product.whisper1_content}</p>
          </div>
        </div>
      </section>

      {/* ── Whisper 2 ── */}
      <section className="bg-[#F9F6F1] w-full pt-0 pb-6 md:pb-10">
        <div className="flex flex-col md:grid md:grid-cols-2 md:min-h-[500px]">
          <div className="bg-[#F9F6F1] flex flex-col justify-center px-6 md:px-20 py-12 md:py-10 order-2 md:order-1">
            <h3 className="text-2xl md:text-3xl text-gray-900 mb-3 font-qlassy tracking-tight">{product.whisper2_heading}</h3>
            <p className="text-[11px] md:text-[12px] tracking-[0.2em] uppercase font-glacial text-gray-800 mb-4 md:mb-5">
              Key notes: {product.key_notes.slice(3).join(', ')}
            </p>
            <p className="text-[15px] md:text-[16px] text-gray-600 font-glacial leading-relaxed max-w-md">{product.whisper2_content}</p>
          </div>
          <div className="relative h-[300px] sm:h-[400px] md:h-auto order-1 md:order-2 bg-amber-900/10 overflow-hidden">
            <video ref={secondVideoRef} autoPlay loop muted playsInline className="absolute inset-0 w-full h-full object-cover">
              <source src={product.video2} type="video/mp4" />
            </video>
            <div className="absolute bottom-4 right-4 md:bottom-6 md:right-6 z-20">
              <button onClick={toggleSecondMute} className="w-10 h-10 border border-white/20 rounded-full flex items-center justify-center bg-black/10 backdrop-blur-md">
                <Icon name={isSecondMuted ? "volume_off" : "volume_up"} className="text-white text-lg" />
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* ── Products Showcase (dynamic) ── */}
      <ProductsShowcase />
    </main>
  );
}

// ─── Dynamic ProductsShowcase ─────────────────────────────────────────────────

function ProductsShowcase() {
  const router = useRouter();

  const [settings, setSettings] = useState<ShowcaseSettings>({
    label:       'Shop Products',
    title:       'Our Collections',
    description: 'Experience the art of premium craftsmanship',
    btn_text:    '',
    btn_link:    '',
  });
  const [showcaseProducts, setShowcaseProducts] = useState<ShowcaseProduct[]>([]);
  const [showcaseLoading, setShowcaseLoading]   = useState(true);

  const [activeProduct, setActiveProduct]         = useState<number | null>(null);
  const [quantityOpenIndex, setQuantityOpenIndex] = useState<number | null>(null);
  const [quantities, setQuantities]               = useState<{ [key: number]: number }>({});
  const scrollRef = useRef<HTMLDivElement>(null);
  const [isPaused, setIsPaused] = useState(false);

  // ✅ Fetch from /api/showcase
  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/showcase.php`, { cache: 'no-store' })
      .then(r => r.ok ? r.json() : null)
      .then(d => {
        if (d?.settings) setSettings(d.settings);
        if (d?.products?.length) setShowcaseProducts(d.products);
      })
      .catch(err => console.error('Showcase fetch error:', err))
      .finally(() => setShowcaseLoading(false));
  }, []);

  useEffect(() => {
    const interval = setInterval(() => {
      if (scrollRef.current && !isPaused && window.innerWidth < 1024) {
        const { scrollLeft, scrollWidth, clientWidth } = scrollRef.current;
        if (scrollLeft + clientWidth >= scrollWidth - 10) { scrollRef.current.scrollTo({ left: 0, behavior: 'smooth' }); }
        else { scrollRef.current.scrollBy({ left: window.innerWidth < 640 ? clientWidth : clientWidth / 2, behavior: 'smooth' }); }
      }
    }, 3000);
    return () => clearInterval(interval);
  }, [isPaused]);

  const getQty       = (i: number) => quantities[i] ?? 1;
  const incrementQty = (i: number) => setQuantities(prev => ({ ...prev, [i]: (prev[i] ?? 1) + 1 }));
  const decrementQty = (i: number) => setQuantities(prev => ({ ...prev, [i]: Math.max(1, (prev[i] ?? 1) - 1) }));

  const handleAddToCartClick = (e: React.MouseEvent, index: number) => {
    e.stopPropagation();
    setQuantityOpenIndex(index);
    setQuantities(prev => ({ ...prev, [index]: 1 }));
  };

  const handleConfirmAdd = (e: React.MouseEvent, p: ShowcaseProduct, index: number) => {
    e.stopPropagation();
    const savedCart = localStorage.getItem('cart');
    let cart: any[] = [];
    try { cart = savedCart ? JSON.parse(savedCart) : []; } catch { cart = []; }
    const qty = getQty(index);
    const idx = cart.findIndex((item: any) => item.name === p.name);
    if (idx !== -1) { cart[idx].qty = Number(cart[idx].qty || 0) + qty; }
    else { cart.push({ name: p.name, image: p.image, price: p.price, qty, category: 'Collection', size: '55ml' }); }
    localStorage.setItem('cart', JSON.stringify(cart));
    window.dispatchEvent(new Event('cartUpdated'));
    window.dispatchEvent(new Event('storage'));
    setQuantityOpenIndex(null);
  };

  const handleBuyNow = (e: React.MouseEvent, p: ShowcaseProduct) => {
    e.stopPropagation();
    localStorage.setItem('checkout_now', JSON.stringify([{ name: p.name, image: p.image, price: p.price, qty: 1, category: 'Collection', size: '55ml' }]));
    router.push('/check-out');
  };

  const containerVariants: Variants = { hidden: { opacity: 0 }, visible: { opacity: 1, transition: { staggerChildren: 0.1 } } };
  const itemVariants:      Variants = { hidden: { opacity: 0, y: 30 }, visible: { opacity: 1, y: 0, transition: { duration: 0.6, ease: 'easeOut' } } };

  return (
    <section
      className="pt-0 pb-12 md:py-16 bg-[#F9F6F1] overflow-hidden"
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
      onTouchStart={() => setIsPaused(true)}
    >
      {/* Section Heading */}
      <motion.div
        initial={{ opacity: 0, y: -20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }}
        className="max-w-7xl mx-auto text-center mb-16 md:mb-28 px-6 space-y-3"
      >
        <span className="text-[13px] tracking-[0.4em] font-qlassy uppercase font-semibold text-gray-600 block mb-4">
          {settings.label}
        </span>
        <h2 className="text-4xl md:text-5xl font-qlassy text-[#1A1A1A] mb-3 tracking-wide leading-none">
          {settings.title}
        </h2>
        <p className="text-lg text-gray-600 font-glacial tracking-wider">{settings.description}</p>
      </motion.div>

      {/* Products */}
      {showcaseLoading ? (
        <div className="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-6 px-6 lg:px-20">
          {[0,1,2,3].map(i => (
            <div key={i} className="flex flex-col items-center gap-4">
              <div className="w-full aspect-square bg-gray-200 animate-pulse rounded-sm" />
              <div className="h-4 w-2/3 bg-gray-200 animate-pulse rounded" />
              <div className="h-4 w-1/3 bg-gray-200 animate-pulse rounded" />
            </div>
          ))}
        </div>
      ) : (
        <motion.div
          ref={scrollRef}
          variants={containerVariants} initial="hidden" whileInView="visible"
          viewport={{ once: true, margin: '-100px' }}
          className="max-w-7xl mx-auto flex flex-row overflow-x-auto snap-x snap-mandatory pb-10 lg:grid lg:grid-cols-4 lg:gap-8 lg:px-20 lg:overflow-visible [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
        >
          {showcaseProducts.slice(0, 4).map((p, index) => {
            const isActive  = activeProduct === index;
            const isBig     = index % 2 === 0;
            const isQtyOpen = quantityOpenIndex === index;
            const qty       = getQty(index);
            return (
              <motion.div
                key={p.id} variants={itemVariants}
                onMouseEnter={() => setActiveProduct(index)} onMouseLeave={() => setActiveProduct(null)}
                className="flex-shrink-0 w-full sm:w-1/2 lg:w-full snap-center flex flex-col items-center group cursor-pointer px-4 lg:px-0"
              >
                <Link href={p.href} className="w-full flex flex-col items-center relative">
                  <div className="relative w-full aspect-square flex items-end justify-center mb-6 bg-[#F9F6F1] overflow-hidden">
                    <div className={`relative transition-all duration-1000 ease-[cubic-bezier(0.23,1,0.32,1)] transform ${isBig ? 'w-[80%] md:w-[75%] h-[90%]' : 'w-[60%] md:w-[55%] h-[70%]'} ${isActive ? '-translate-y-1.5' : 'translate-y-0'} md:group-hover:-translate-y-2`}>
                      <img src={p.image} alt={p.name}
                        className="absolute inset-0 w-full h-full object-contain object-bottom drop-shadow-[0_10px_20px_rgba(0,0,0,0.05)] transition-all duration-700 group-hover:drop-shadow-[0_15px_30px_rgba(0,0,0,0.1)]" />
                    </div>
                  </div>
                  <div className="text-center space-y-1 md:space-y-2 w-full px-4">
                    <h3 className="text-[13px] md:text-[14px] font-semibold font-glacial uppercase tracking-[0.15em] md:tracking-[0.2em] text-gray-800 transition-colors duration-500 group-hover:text-black leading-tight">{p.name}</h3>
                    <div className="flex justify-center items-center">
                      <div className="flex items-center gap-1.5 border border-black/5 text-[#00002e] text-[12px] md:text-[14px] px-2 py-1 rounded-full">
                        <span className="font-bold">{Number(p.rating).toFixed(1)}</span>
                        <Star size={12} fill="currentColor" />
                      </div>
                    </div>
                    <p className="text-[14px] md:text-base font-bold tracking-tight text-black/80">{p.price_range}</p>
                  </div>
                </Link>

                <div className="w-full px-2 mt-4 flex justify-center">
                  <div className={`flex flex-col gap-1.5 w-full max-w-[320px] md:max-w-[180px] transition-all duration-700 ease-out ${isActive ? 'md:max-h-40 md:opacity-100 md:pt-6' : 'opacity-100 md:max-h-0 md:opacity-0 md:pt-0'}`}>
                    <div className="flex flex-row md:flex-col gap-1.5 md:gap-2 w-full">
                      {isQtyOpen ? (
                        <motion.div initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }} transition={{ duration: 0.15, ease: 'easeOut' }}
                          className="flex items-center bg-white border border-black/10 rounded-full px-1 py-1 shadow-md font-glacial flex-1 md:w-full justify-center gap-0 min-w-0"
                          onClick={(e) => e.stopPropagation()}>
                          <button onClick={(e) => { e.stopPropagation(); decrementQty(index); }} className="w-5 h-5 flex items-center justify-center text-black/50 hover:text-black text-base leading-none font-light flex-shrink-0">−</button>
                          <span className="w-5 text-center text-[11px] font-semibold text-black select-none flex-shrink-0">{qty}</span>
                          <button onClick={(e) => { e.stopPropagation(); incrementQty(index); }} className="w-5 h-5 flex items-center justify-center text-black/50 hover:text-black text-base leading-none font-light flex-shrink-0">+</button>
                          <button onClick={(e) => handleConfirmAdd(e, p, index)} className="bg-black text-white text-[8px] md:text-[9px] font-bold uppercase tracking-widest px-2 md:px-4 py-1 md:py-1.5 rounded-full ml-1 flex-shrink-0">ADD</button>
                          <button onClick={(e) => { e.stopPropagation(); setQuantityOpenIndex(null); }} className="w-6 h-6 flex items-center justify-center text-black/30 hover:text-black text-lg leading-none transition-colors ml-0.5">×</button>
                        </motion.div>
                      ) : (
                        <button onClick={(e) => handleAddToCartClick(e, index)}
                          className="flex-1 md:w-full border border-black text-black text-[9px] sm:text-[11px] font-bold font-glacial uppercase py-3 rounded-sm hover:bg-black hover:text-white transition-all tracking-widest flex items-center justify-center gap-1.5 sm:gap-2">
                          <ShoppingBag className="w-3.5 h-3.5 md:w-4 md:h-4" /><span className="whitespace-nowrap">Add To Cart</span>
                        </button>
                      )}
                      <button onClick={(e) => handleBuyNow(e, p)}
                        className="flex-1 md:w-full border border-black text-black text-[9px] sm:text-[11px] font-glacial font-bold uppercase py-3 rounded-sm hover:bg-black hover:text-white transition-all tracking-widest flex items-center justify-center gap-1.5 sm:gap-2">
                        <CreditCard className="w-3.5 h-3.5 md:w-4 md:h-4" /><span className="whitespace-nowrap">Buy Now</span>
                      </button>
                    </div>
                  </div>
                </div>
              </motion.div>
            );
          })}
        </motion.div>
      )}
    </section>
  );
}