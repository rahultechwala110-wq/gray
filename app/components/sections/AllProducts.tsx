'use client';

import Image from 'next/image';
import { ShoppingBag, CreditCard, SlidersHorizontal, ChevronDown, Check, Star } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';

interface Product {
  id?: number;
  name: string;
  image: string;
  price: number;
  price_range?: string;
  rating: number;
  category: string;
  href: string;
}

const fallbackProducts: Product[] = [
  // Man
  { name: 'Brave',      image: '/products/product-1.png',  price: 4200, rating: 5, category: 'Man',       href: '/brave-mens-perfume-55ml' },
  { name: 'Boss',       image: '/products/product-2.png',  price: 4500, rating: 5, category: 'Man',       href: '/boss-mens-perfume-55m' },
  { name: 'Gentle',     image: '/products/product-3.png',  price: 4200, rating: 5, category: 'Man',       href: '/gentle-mens-perfume-55ml' },
  { name: 'Bold',       image: '/products/product-4.png',  price: 4800, rating: 5, category: 'Man',       href: '/gold-men-perfume-55ml' },
  { name: 'Generous',   image: '/products/product-5.png',  price: 4200, rating: 5, category: 'Man',       href: '/generous-mens-perfume-55ml' },
  { name: 'Groomed',    image: '/products/product-6.png',  price: 4200, rating: 4, category: 'Man',       href: '/groomed-mens-perfume-55m' },
  // Woman
  { name: 'Bliss',      image: '/products/product-7.png',  price: 3500, rating: 5, category: 'Woman',     href: '/bliss-womens-perfume-55ml' },
  { name: 'Gorgeous',   image: '/products/product-8.png',  price: 3800, rating: 5, category: 'Woman',     href: '/gorgeous-womens-perfume-55ml' },
  { name: 'Braveheart', image: '/products/product-9.png',  price: 4200, rating: 5, category: 'Woman',     href: '/braveheart-womens-perfume-55ml' },
  { name: 'Glorious',   image: '/products/product-10.png', price: 4200, rating: 5, category: 'Woman',     href: '/glorious-womens-perfume-55ml' },
  { name: 'Brilliance', image: '/products/product-11.png', price: 3800, rating: 5, category: 'Woman',     href: '/brilliance-womens-perfume-55ml' },
  { name: 'Gifted',     image: '/products/product-12.png', price: 4200, rating: 5, category: 'Woman',     href: '/gifted-womens-perfume-55ml' },
  // Aroma Pod
  { name: 'B612',       image: '/products/product-13.png', price: 2500, rating: 5, category: 'Aroma Pod', href: '/aroma-pod/b612' },
  { name: 'Bulge',      image: '/products/product-14.png', price: 2500, rating: 5, category: 'Aroma Pod', href: '/aroma-pod/bulge' },
  { name: 'Brahe',      image: '/products/product-15.png', price: 2500, rating: 5, category: 'Aroma Pod', href: '/aroma-pod/brahe' },
  { name: 'Glese',      image: '/products/product-16.png', price: 2500, rating: 5, category: 'Aroma Pod', href: '/aroma-pod/glese' },
  { name: 'Ganymede',   image: '/products/product-17.png', price: 2500, rating: 5, category: 'Aroma Pod', href: '/aroma-pod/ganymede' },
  { name: 'Gaspra',     image: '/products/product-18.png', price: 2500, rating: 5, category: 'Aroma Pod', href: '/aroma-pod/gaspra' },
];

const sortOptions = ['Newest', 'Price: Low to High', 'Price: High to Low', 'Top Rated'];
const VALID_CATEGORIES = ['All', 'Man', 'Woman', 'Aroma Pod'];

function getImageSrc(image: string) {
  if (!image) return '';
  if (image.startsWith('/') || image.startsWith('http')) return image;
  return `/products/${image}`;
}

function getSizeClass(category: string): string {
  if (category === 'Man')       return 'w-[80%] md:w-[75%] h-[90%]';
  if (category === 'Woman')     return 'w-[60%] md:w-[55%] h-[70%]';
  if (category === 'Aroma Pod') return 'w-[80%] md:w-[75%] h-[90%]';
  return 'w-[75%] h-[85%]';
}

const GRID_CLASS = "grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-4 sm:gap-x-6 gap-y-10 md:gap-x-8 md:gap-y-20";

export default function Products() {
  const router     = useRouter();
  const searchParams = useSearchParams();

  const [allProducts, setAllProducts]           = useState<Product[]>(fallbackProducts);
  const [activeProduct, setActiveProduct]       = useState<string | null>(null);
  const [showFilters, setShowFilters]           = useState(false);
  const [showSort, setShowSort]                 = useState(false);
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [selectedSort, setSelectedSort]         = useState('Newest');
  const [quantityOpenKey, setQuantityOpenKey]   = useState<string | null>(null);
  const [quantities, setQuantities]             = useState<{ [key: string]: number }>({});

  // ── Read ?category= from URL on mount & when URL changes ─────────────────
  useEffect(() => {
    const param = searchParams.get('category');
    if (param && VALID_CATEGORIES.includes(param)) {
      setSelectedCategory(param);
    }
  }, [searchParams]);

  useEffect(() => {
    fetch('/api/showcase')
      .then(r => r.json())
      .then(({ products: prods }) => {
        if (prods?.length) {
          setAllProducts(prods.map((p: Product) => ({
            ...p,
            image:  getImageSrc(p.image),
            href:   p.href || '/product',
            rating: Number(p.rating) || 5,
          })));
        }
      })
      .catch(() => {});
  }, []);

  const categories = ['All', ...Array.from(new Set(allProducts.map(p => p.category).filter(Boolean))) as string[]];

  const getQty       = (name: string) => quantities[name] ?? 1;
  const incrementQty = (name: string) => setQuantities(prev => ({ ...prev, [name]: (prev[name] ?? 1) + 1 }));
  const decrementQty = (name: string) => setQuantities(prev => ({ ...prev, [name]: Math.max(1, (prev[name] ?? 1) - 1) }));

  // Update URL when category changes via filter button
  const handleCategoryChange = (cat: string) => {
    setSelectedCategory(cat);
    setShowFilters(false);
    const params = new URLSearchParams(searchParams.toString());
    if (cat === 'All') {
      params.delete('category');
    } else {
      params.set('category', cat);
    }
    router.replace(`/all-products${params.toString() ? `?${params.toString()}` : ''}`, { scroll: false });
  };

  const handleAddToCartClick = (e: React.MouseEvent, key: string, productName: string) => {
    e.stopPropagation();
    setQuantityOpenKey(key);
    setQuantities(prev => ({ ...prev, [productName]: 1 }));
  };

  const handleConfirmAdd = (e: React.MouseEvent, product: Product) => {
    e.stopPropagation();
    const savedCart = localStorage.getItem('cart');
    let cart: any[] = [];
    try { cart = savedCart ? JSON.parse(savedCart) : []; } catch { cart = []; }
    const qty = getQty(product.name);
    const idx = cart.findIndex((i: any) => i.name === product.name);
    if (idx !== -1) {
      cart[idx].qty = Number(cart[idx].qty || 0) + qty;
    } else {
      cart.push({ name: product.name, image: product.image, price: product.price, qty, category: product.category, size: '100ml EDP' });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    window.dispatchEvent(new Event('cartUpdated'));
    window.dispatchEvent(new Event('storage'));
    setQuantityOpenKey(null);
  };

  const handleBuyNow = (e: React.MouseEvent, product: Product) => {
    e.stopPropagation();
    localStorage.setItem('checkout_now', JSON.stringify([{
      name: product.name, image: product.image, price: product.price,
      qty: 1, category: product.category, size: '100ml EDP'
    }]));
    router.push('/check-out');
  };

  const applySort = (list: Product[]) => [...list].sort((a, b) => {
    if (selectedSort === 'Price: Low to High') return a.price - b.price;
    if (selectedSort === 'Price: High to Low') return b.price - a.price;
    if (selectedSort === 'Top Rated')          return b.rating - a.rating;
    return 0;
  });

  const isAromaPodOnly = selectedCategory === 'Aroma Pod';
  const isAllView      = selectedCategory === 'All';

  const mainProducts = applySort(
    allProducts.filter(p =>
      p.category !== 'Aroma Pod' &&
      (isAllView || selectedCategory === p.category)
    )
  );

  const aromaPodProducts = applySort(
    allProducts.filter(p => p.category === 'Aroma Pod')
  );

  const totalCount = isAromaPodOnly
    ? aromaPodProducts.length
    : isAllView
      ? allProducts.length
      : mainProducts.length;

  const ProductCard = ({ product, cardKey }: { product: Product; cardKey: string }) => {
    const isActive  = activeProduct === cardKey;
    const isQtyOpen = quantityOpenKey === cardKey;
    const qty       = getQty(product.name);
    const sizeClass = getSizeClass(product.category);

    return (
      <motion.div
        layout
        key={cardKey}
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        exit={{ opacity: 0 }}
        onMouseEnter={() => setActiveProduct(cardKey)}
        onMouseLeave={() => { setActiveProduct(null); if (!isQtyOpen) setQuantityOpenKey(null); }}
        className="w-full flex flex-col items-center group cursor-pointer"
      >
        <Link href={product.href} className="w-full flex flex-col items-center relative">
          <div className="relative w-full aspect-square flex items-end justify-center mb-4 md:mb-6 bg-[#F9F6F1] overflow-hidden">
            <div className={`relative transition-all duration-1000 ease-[cubic-bezier(0.23,1,0.32,1)] transform ${sizeClass} ${isActive ? '-translate-y-1.5' : 'translate-y-0'}`}>
              {getImageSrc(product.image) && (
                <Image
                  src={getImageSrc(product.image)}
                  alt={product.name}
                  fill
                  className="object-contain object-bottom drop-shadow-[0_10px_20px_rgba(0,0,0,0.05)] transition-all duration-700 md:group-hover:drop-shadow-[0_15px_30px_rgba(0,0,0,0.1)]"
                  sizes="(max-width: 768px) 50vw, 25vw"
                />
              )}
            </div>
          </div>

          <div className="text-center space-y-1 md:space-y-2 w-full px-4 font-glacial">
            <h3 className="text-[13px] md:text-[14px] font-bold font-qlassy uppercase tracking-[0.15em] md:tracking-[0.2em] text-gray-800 transition-colors duration-500 group-hover:text-black leading-tight">
              {product.name}
            </h3>
            <div className="flex justify-center items-center">
              <div className="flex items-center gap-1.5 border border-black/5 text-[#00002e] text-[12px] md:text-[14px] px-2 py-1 rounded-full">
                <span className="font-bold">{Number(product.rating).toFixed(1)}</span>
                <span className="text-[11px] md:text-[13px]"><Star size={12} fill="currentColor" /></span>
              </div>
            </div>
            <p className="text-[14px] md:text-base font-bold tracking-tight text-black/80">
              ₹{Number(product.price).toLocaleString()}
            </p>
          </div>
        </Link>

        <div className="w-full px-1 md:px-2 mt-3 md:mt-4 flex justify-center">
          <div className={`flex flex-col gap-1.5 md:gap-2 w-full max-w-[280px] md:max-w-[180px] transition-all duration-700 ease-out
            ${isActive || isQtyOpen ? 'md:max-h-40 md:opacity-100 md:pt-6' : 'opacity-100 md:max-h-0 md:opacity-0 md:pt-0'}`}
          >
            {isQtyOpen ? (
              <motion.div
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                className="flex items-center bg-white border border-black/10 rounded-full px-1 py-1 shadow-md font-glacial w-full justify-center gap-0"
                onClick={(e) => e.stopPropagation()}
              >
                <button onClick={(e) => { e.stopPropagation(); decrementQty(product.name); }} className="w-5 h-5 md:w-6 md:h-6 flex items-center justify-center text-black/50 hover:text-black text-base md:text-xl leading-none font-light flex-shrink-0">−</button>
                <span className="w-5 md:w-6 text-center text-[11px] md:text-[13px] font-semibold text-black select-none flex-shrink-0">{qty}</span>
                <button onClick={(e) => { e.stopPropagation(); incrementQty(product.name); }} className="w-5 h-5 md:w-6 md:h-6 flex items-center justify-center text-black/50 hover:text-black text-base md:text-xl leading-none font-light flex-shrink-0">+</button>
                <button onClick={(e) => handleConfirmAdd(e, product)} className="bg-black text-white text-[8px] md:text-[10px] font-bold uppercase tracking-widest px-2 md:px-3 py-1 md:py-1.5 rounded-full ml-1 flex-shrink-0">ADD</button>
                <button onClick={(e) => { e.stopPropagation(); setQuantityOpenKey(null); }} className="w-6 h-6 flex items-center justify-center text-black/30 hover:text-black text-lg leading-none transition-colors ml-0.5">×</button>
              </motion.div>
            ) : (
              <button
                onClick={(e) => handleAddToCartClick(e, cardKey, product.name)}
                className="flex-1 w-full border border-black text-black text-[9px] lg:text-[11px] font-bold font-glacial uppercase py-2.5 sm:py-3 rounded-sm hover:bg-black hover:text-white transition-all tracking-widest flex items-center justify-center gap-1.5 sm:gap-2"
              >
                <ShoppingBag className="w-3 h-3 md:w-4 md:h-4" />
                <span className="whitespace-nowrap">Add To Cart</span>
              </button>
            )}

            <button
              onClick={(e) => handleBuyNow(e, product)}
              className="flex-1 w-full border border-black text-black text-[9px] lg:text-[11px] font-bold font-glacial uppercase py-2.5 sm:py-3 rounded-sm hover:bg-black hover:text-white transition-all tracking-widest flex items-center justify-center gap-1.5 sm:gap-2"
            >
              <CreditCard className="w-3 h-3 md:w-4 md:h-4" />
              <span className="whitespace-nowrap">Buy Now</span>
            </button>
          </div>
        </div>
      </motion.div>
    );
  };

  return (
    <section className="pt-24 pb-20 md:pt-40 md:pb-40 px-4 sm:px-6 md:px-10 lg:px-20 bg-[#F9F6F1] overflow-hidden min-h-screen">

      {/* Filter & Sort Bar */}
      <div className="max-w-7xl mx-auto mb-12 md:mb-16 border-b border-black/5 pb-6 md:pb-8">
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-6">
          <div className="space-y-4">
            <h3 className="text-2xl md:text-4xl font-qlassy text-[#1A1A1A] mb-1 md:mb-3 tracking-wide leading-none">
              Collections — <span className="opacity-50">{selectedCategory}</span>
            </h3>

            <div className="relative">
              <button
                onClick={() => { setShowFilters(!showFilters); setShowSort(false); }}
                className={`flex items-center gap-2 border px-4 md:px-6 py-2 md:py-2.5 text-[10px] md:text-[11px] uppercase tracking-[0.2em] transition-all duration-300 font-bold ${
                  showFilters ? 'bg-black text-white' : 'border-black/10 hover:bg-black hover:text-white'
                }`}
              >
                <SlidersHorizontal size={14} />
                {showFilters ? 'Close' : selectedCategory === 'All' ? 'Filter' : selectedCategory}
              </button>

              <AnimatePresence>
                {showFilters && (
                  <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 10 }}
                    className="absolute top-full left-0 mt-4 bg-white border border-black/5 shadow-2xl z-50 min-w-[220px] p-2"
                  >
                    {categories.map((cat) => (
                      <button
                        key={cat}
                        onClick={() => handleCategoryChange(cat)}
                        className="w-full text-left px-5 py-4 text-[11px] uppercase tracking-widest hover:bg-gray-50 flex justify-between items-center transition-colors font-bold"
                      >
                        {cat}
                        {selectedCategory === cat && <Check size={14} className="text-zinc-500" />}
                      </button>
                    ))}
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
          </div>

          <div className="flex items-center justify-between md:justify-end gap-6 md:gap-10 text-sm">
            <span className="opacity-60 text-gray-600 font-medium text-xs md:text-sm">
              {totalCount} items found
            </span>

            <div className="relative">
              <div
                onClick={() => { setShowSort(!showSort); setShowFilters(false); }}
                className="group cursor-pointer flex items-center gap-2 border-b border-transparent hover:border-black pb-1 transition-all"
              >
                <span className="text-gray-400 uppercase text-[9px] md:text-[10px] tracking-widest font-bold">Sort by</span>
                <span className="font-semibold text-gray-800 text-xs md:text-sm">{selectedSort}</span>
                <ChevronDown size={14} className={`transition-transform duration-300 ${showSort ? 'rotate-180' : ''}`} />
              </div>

              <AnimatePresence>
                {showSort && (
                  <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 10 }}
                    className="absolute top-full right-0 mt-4 bg-white border border-black/5 shadow-2xl z-50 min-w-[180px] p-2"
                  >
                    {sortOptions.map((option) => (
                      <button
                        key={option}
                        onClick={() => { setSelectedSort(option); setShowSort(false); }}
                        className="w-full text-right px-5 py-3 text-[10px] uppercase tracking-widest hover:bg-gray-50 flex justify-between items-center transition-colors font-bold"
                      >
                        {selectedSort === option && <Check size={12} className="text-zinc-500" />}
                        <span className="ml-auto">{option}</span>
                      </button>
                    ))}
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
          </div>
        </div>
      </div>

      {/* ── Men + Women Grid ─────────────────────────────────────────────── */}
      {!isAromaPodOnly && mainProducts.length > 0 && (
        <motion.div layout className="max-w-7xl mx-auto">
          <motion.div layout className={GRID_CLASS}>
            <AnimatePresence mode="popLayout">
              {mainProducts.map((product) => (
                <ProductCard
                  key={`main-${product.name}`}
                  product={product}
                  cardKey={`main-${product.name}`}
                />
              ))}
            </AnimatePresence>
          </motion.div>
        </motion.div>
      )}

      {/* ── Aroma Pod Section ────────────────────────────────────────────── */}
      {(isAllView || isAromaPodOnly) && aromaPodProducts.length > 0 && (
        <motion.div
          layout
          className="max-w-7xl mx-auto mt-10 md:mt-20"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
        >
          <motion.div layout className={GRID_CLASS}>
            <AnimatePresence mode="popLayout">
              {aromaPodProducts.map((product) => (
                <ProductCard
                  key={`aroma-${product.name}`}
                  product={product}
                  cardKey={`aroma-${product.name}`}
                />
              ))}
            </AnimatePresence>
          </motion.div>
        </motion.div>
      )}

    </section>
  );
}