'use client';

// ✅ Fixed: useEffect import kora hoyeche
import { useState, useEffect } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { X, Plus, Minus, ArrowLeft, ShoppingBag, Gift, Truck, ArrowRight } from 'lucide-react';

interface CartItem {
  id: number | string;
  name: string;
  category: string;
  size: string;
  price: number;
  qty: number;
  image: string;
}

interface RecommendedItem {
  name: string;
  image: string;
}

const recommendations: RecommendedItem[] = [
  { name: 'Boss',     image: '/mega-menu/02.jpg' },
  { name: 'Gorgeous', image: '/mega-menu/08.jpg' },
  { name: 'Bulge',    image: '/mega-menu/14.jpg' },
];

export default function CartPage() {
  const [items, setItems] = useState<CartItem[]>([]);
  const [promoCode, setPromoCode] = useState<string>('');
  const [promoApplied, setPromoApplied] = useState<boolean>(false);
  const [removing, setRemoving] = useState<number | string | null>(null);

  useEffect(() => {
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
      try {
        const data = JSON.parse(savedCart);
        const formattedData = data.map((item: any, index: number) => ({
          id: item.id || item.name || index, 
          name: item.name,
          category: item.category || 'Collection',
          size: item.size || '100ml EDP',
          price: item.price || 4200, 
          qty: Number(item.qty || item.quantity || 1),
          image: item.image
        }));
        setItems(formattedData);
      } catch (error) {
        console.error("Cart load error:", error);
      }
    }
  }, []);

  const updateQty = (id: number | string, delta: number): void => {
    const updatedItems = items.map((item) =>
      item.id === id ? { ...item, qty: Math.max(1, item.qty + delta) } : item
    );
    setItems(updatedItems);
    localStorage.setItem('cart', JSON.stringify(updatedItems));
    window.dispatchEvent(new Event('cartUpdated'));
  };

  const removeItem = (id: number | string): void => {
    setRemoving(id);
    setTimeout(() => {
      const filteredItems = items.filter((item) => item.id !== id);
      setItems(filteredItems);
      localStorage.setItem('cart', JSON.stringify(filteredItems));
      window.dispatchEvent(new Event('cartUpdated'));
      setRemoving(null);
    }, 400);
  };

  const applyPromo = (): void => {
    if (promoCode.trim().toLowerCase() === 'gray10') setPromoApplied(true);
  };

  const subtotal = items.reduce((acc, item) => acc + item.price * item.qty, 0);
  const discount = promoApplied ? Math.round(subtotal * 0.1) : 0;
  const shipping  = subtotal > 5000 || items.length === 0 ? 0 : 299;
  const total     = subtotal - discount + shipping;

  return (
    <div className="min-h-screen" style={{ background: '#F9F6F1', color: '#1a1a1a' }}>

      <div
        className="fixed top-0 right-0 w-[500px] h-[500px] rounded-full pointer-events-none z-0"
        style={{
          background: 'radial-gradient(circle, rgba(180,155,110,0.07) 0%, transparent 70%)',
          transform: 'translate(30%, -30%)',
        }}
      />

      <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 pt-28 pb-20">

        <Link
          href="/all-products"
          className="inline-flex items-center gap-2 text-[11px] tracking-[0.35em] uppercase transition-colors duration-300 mb-12 group font-glacial"
          style={{ color: 'rgba(26,26,26,0.55)' }}
          onMouseEnter={(e) => ((e.currentTarget as HTMLAnchorElement).style.color = '#1a1a1a')}
          onMouseLeave={(e) => ((e.currentTarget as HTMLAnchorElement).style.color = 'rgba(26,26,26,0.55)')}
        >
          <ArrowLeft className="w-3.5 h-3.5 transition-transform duration-300 group-hover:-translate-x-1" />
          Continue Shopping
        </Link>

        <div className="flex items-end justify-between mb-12 pb-8" style={{ borderBottom: '1px solid rgba(26,26,26,0.12)' }}>
          <div>
            <p className="text-[10px] tracking-[0.5em] uppercase mb-2 font-glacial" style={{ color: 'rgba(26,26,26,0.55)' }}>
              Your Selection
            </p>
            <h1 className="text-4xl sm:text-5xl font-light tracking-tight font-qlassy" style={{ color: '#1a1a1a' }}>
              Cart
            </h1>
          </div>
          <div className="flex items-center gap-2 text-[12px] tracking-[0.3em] uppercase font-glacial" style={{ color: 'rgba(26,26,26,0.55)' }}>
            <ShoppingBag className="w-4 h-4" />
            <span>{items.length} {items.length === 1 ? 'item' : 'items'}</span>
          </div>
        </div>

        {items.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-32 text-center">
            <ShoppingBag className="w-16 h-16 mb-6" style={{ color: 'rgba(26,26,26,0.15)' }} />
            <p className="text-[12px] tracking-[0.4em] uppercase mb-2 font-glacial" style={{ color: 'rgba(26,26,26,0.5)' }}>Your cart is empty</p>
            <Link href="/" className="text-[11px] tracking-[0.4em] uppercase px-8 py-3 transition-all duration-300 font-glacial" style={{ border: '1px solid rgba(26,26,26,0.3)', color: '#1a1a1a' }}>Explore Collection</Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-10 xl:gap-14">

            <div className="space-y-1">
              {items.map((item, index) => (
                <div key={item.id}>
                  <div
                    className="group relative flex gap-5 sm:gap-7 p-5 sm:p-6 rounded-sm transition-all duration-300"
                    style={{
                      opacity: removing === item.id ? 0 : 1,
                      transform: removing === item.id ? 'translateX(30px)' : 'translateX(0)',
                      background: 'rgba(255,255,255,0.7)',
                      border: '1px solid rgba(26,26,26,0.09)',
                    }}
                    onMouseEnter={(e) => {
                      (e.currentTarget as HTMLDivElement).style.background = '#ffffff';
                      (e.currentTarget as HTMLDivElement).style.borderColor = 'rgba(26,26,26,0.15)';
                    }}
                    onMouseLeave={(e) => {
                      (e.currentTarget as HTMLDivElement).style.background = 'rgba(255,255,255,0.7)';
                      (e.currentTarget as HTMLDivElement).style.borderColor = 'rgba(26,26,26,0.09)';
                    }}
                  >
                    <div className="relative w-20 h-20 sm:w-24 sm:h-24 flex-shrink-0 overflow-hidden rounded-sm bg-white p-2 flex items-center justify-center" style={{ border: '1px solid rgba(26,26,26,0.1)' }}>
                      <div className="relative w-full h-full">
                        <Image 
                          src={item.image} 
                          alt={item.name} 
                          fill 
                          className="object-contain transition-transform duration-500 group-hover:scale-105" 
                        />
                      </div>
                    </div>

                    <div className="flex-1 flex flex-col justify-between py-1 min-w-0">
                      <div>
                        <p className="text-[10px] tracking-[0.4em] uppercase mb-0.5 font-glacial" style={{ color: 'rgba(26,26,26,0.55)' }}>{item.category}</p>
                        <h3 className="text-xl sm:text-2xl font-light tracking-wide mb-0.5 font-glacial" style={{ color: '#1a1a1a' }}>{item.name}</h3>
                        <p className="text-[11px] tracking-[0.2em] font-glacial" style={{ color: 'rgba(26,26,26,0.55)' }}>{item.size}</p>
                      </div>

                      <div className="flex items-center justify-between mt-4">
                        <div className="flex items-center rounded-sm overflow-hidden" style={{ border: '1px solid rgba(26,26,26,0.15)' }}>
                          <button onClick={() => updateQty(item.id, -1)} className="w-8 h-8 flex items-center justify-center transition-all" style={{ color: 'rgba(26,26,26,0.55)' }}>
                            <Minus className="w-3 h-3" />
                          </button>
                          <span className="w-8 h-8 flex items-center justify-center text-[12px] font-glacial" style={{ borderLeft: '1px solid rgba(26,26,26,0.1)', borderRight: '1px solid rgba(26,26,26,0.1)', color: '#1a1a1a' }}>{item.qty}</span>
                          <button onClick={() => updateQty(item.id, 1)} className="w-8 h-8 flex items-center justify-center transition-all" style={{ color: 'rgba(26,26,26,0.55)' }}>
                            <Plus className="w-3 h-3" />
                          </button>
                        </div>
                        <p className="text-[18px] sm:text-[20px] font-light tracking-tight font-glacial" style={{ color: '#1a1a1a' }}>₹{(item.price * item.qty).toLocaleString('en-IN')}</p>
                      </div>
                    </div>

                    <button onClick={() => removeItem(item.id)} className="self-start p-1.5 transition-colors duration-200 flex-shrink-0 mt-0" style={{ color: 'rgba(26,26,26,0.35)' }}
                      onMouseEnter={(e) => ((e.currentTarget as HTMLButtonElement).style.color = '#1a1a1a')}
                      onMouseLeave={(e) => ((e.currentTarget as HTMLButtonElement).style.color = 'rgba(26,26,26,0.35)')}
                    >
                      <X className="w-3.5 h-3.5" />
                    </button>
                  </div>
                  {index < items.length - 1 && <div className="h-px mx-1" style={{ background: 'rgba(26,26,26,0.06)' }} />}
                </div>
              ))}

              <div className="flex flex-wrap gap-4 sm:gap-6 mt-8 p-5 rounded-sm" style={{ background: 'rgba(26,26,26,0.03)', border: '1px solid rgba(26,26,26,0.09)' }}>
                <div className="flex items-center gap-2.5 text-[11px] tracking-[0.2em] uppercase font-glacial" style={{ color: 'rgba(26,26,26,0.6)' }}><Truck className="w-4 h-4" /> Free shipping above ₹5,000</div>
                <div className="flex items-center gap-2.5 text-[11px] tracking-[0.2em] uppercase font-glacial" style={{ color: 'rgba(26,26,26,0.6)' }}><Gift className="w-4 h-4" /> Complimentary gift wrapping</div>
              </div>
            </div>

            <div className="lg:sticky lg:top-28 self-start">
              <div className="rounded-sm p-7" style={{ background: '#ffffff', border: '1px solid rgba(26,26,26,0.1)' }}>
                <p className="text-[10px] tracking-[0.5em] uppercase mb-6 font-glacial" style={{ color: 'rgba(26,26,26,0.6)' }}>Order Summary</p>
                <div className="space-y-4 text-[15px] mb-6 font-glacial">
                  <div className="flex justify-between" style={{ color: 'rgba(26,26,26,0.7)' }}><span>Subtotal</span><span>₹{subtotal.toLocaleString('en-IN')}</span></div>
                  {promoApplied && <div className="flex justify-between" style={{ color: '#357933' }}><span>Discount (10%)</span><span>− ₹{discount.toLocaleString('en-IN')}</span></div>}
                  <div className="flex justify-between" style={{ color: 'rgba(26,26,26,0.7)' }}><span>Shipping</span><span>{shipping === 0 ? <span style={{ color: '#357933' }}>Free</span> : `₹${shipping}`}</span></div>
                </div>
                <div className="h-px mb-6" style={{ background: 'rgba(26,26,26,0.1)' }} />
                <div className="flex justify-between items-baseline mb-8">
                  <p className="text-[10px] tracking-[0.5em] uppercase font-glacial" style={{ color: 'rgba(26,26,26,0.6)' }}>Total</p>
                  <p className="text-2xl font-light font-glacial" style={{ color: '#1a1a1a' }}>₹{total.toLocaleString('en-IN')}</p>
                </div>

                <div className="mb-6">
                  <p className="text-[9px] tracking-[0.4em] uppercase mb-3 font-glacial" style={{ color: 'rgba(26,26,26,0.55)' }}>Promo Code</p>
                  <div className="flex gap-2">
                    {/* Fixed: min-w-0 added to allow input to shrink properly */}
                    <input type="text" value={promoCode} onChange={(e) => setPromoCode(e.target.value)} placeholder="Enter code" disabled={promoApplied} className="flex-1 min-w-0 bg-transparent px-3 sm:px-4 py-2.5 text-[12px] tracking-[0.15em] sm:tracking-[0.2em] focus:outline-none font-glacial" style={{ border: '1px solid rgba(26,26,26,0.2)', color: '#1a1a1a' }} onKeyDown={(e) => e.key === 'Enter' && applyPromo()} />
                    
                    {/* Fixed: flex-shrink-0 and whitespace-nowrap added to prevent text cutoff */}
                    <button onClick={applyPromo} disabled={promoApplied || promoCode.trim() === ''} className="flex-shrink-0 whitespace-nowrap px-3 sm:px-4 py-2.5 text-[10px] tracking-[0.2em] sm:tracking-[0.3em] uppercase transition-all duration-300 disabled:opacity-40 font-glacial relative overflow-hidden group" style={{ background: '#1a1a1a', color: '#F9F6F1' }}>
                      <span className="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-out" style={{ background: '#2e2e2e' }} />
                      <span className="relative z-10">{promoApplied ? 'Applied' : 'Apply'}</span>
                    </button>
                  </div>
                  {promoApplied ? (
                    <p className="text-[10px] tracking-[0.2em] mt-2 font-glacial" style={{ color: '#357933' }}>✦ GRAY10 applied — 10% off</p>
                  ) : (
                    <p className="text-[10px] tracking-[0.15em] mt-2 font-glacial" style={{ color: 'rgba(26,26,26,0.55)' }}>Try: GRAY10</p>
                  )}
                </div>

                {/* ✅ Proceed to Checkout — onClick e checkout_now clear kora hoche */}
                <Link
                  href="/check-out"
                  onClick={() => localStorage.removeItem('checkout_now')}
                  className="w-full py-4 text-[12px] tracking-[0.3em] sm:tracking-[0.4em] uppercase rounded-sm flex items-center justify-between px-4 sm:px-6 group font-glacial relative overflow-hidden"
                  style={{ background: '#1a1a1a', color: '#F9F6F1', display: 'flex' }}
                >
                  <span className="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-out" style={{ background: '#2e2e2e' }} />
                  <span className="relative z-10">Proceed to Checkout</span>
                  <ArrowRight className="relative z-10 w-4 h-4 transition-transform duration-300 group-hover:translate-x-2" />
                </Link>
                <p className="text-center text-[9px] tracking-[0.3em] uppercase mt-4 font-glacial" style={{ color: 'rgba(26,26,26,0.55)' }}>Secured & Encrypted Checkout</p>
              </div>

              <div className="mt-6 p-5 rounded-sm" style={{ border: '1px solid rgba(26,26,26,0.09)', background: 'rgba(255,255,255,0.5)' }}>
                <p className="text-[9px] tracking-[0.45em] uppercase mb-4 font-glacial" style={{ color: 'rgba(26,26,26,0.55)' }}>You Might Also Like</p>
                <div className="flex gap-3">
                  {recommendations.map((rec) => (
                    <button key={rec.name} className="flex-1 group text-center">
                      <div className="relative h-20 mb-2 overflow-hidden rounded-sm bg-white p-1.5 flex items-center justify-center" style={{ border: '1px solid rgba(26,26,26,0.1)' }}>
                        <Image src={rec.image} alt={rec.name} fill className="object-cover transition-transform duration-500 group-hover:scale-110" />
                      </div>
                      <p className="text-[12px] tracking-[0.2em] font-glacial text-black/60">{rec.name}</p>
                    </button>
                  ))}
                </div>
              </div>
            </div>

          </div>
        )}
      </div>

      <style jsx global>{`
        * { box-sizing: border-box; }
        input::placeholder { color: rgba(26,26,26,0.35); }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #F9F6F1; }
        ::-webkit-scrollbar-thumb { background: rgba(26,26,26,0.15); border-radius: 2px; }
      `}</style>
    </div>
  );
}