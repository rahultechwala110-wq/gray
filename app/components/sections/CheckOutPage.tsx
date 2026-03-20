'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { ArrowLeft, ArrowRight, ChevronDown, ChevronUp, Check, Truck, Lock, CreditCard, Smartphone, Package } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';

interface CartItem {
  id: number | string;
  name: string;
  category: string;
  size: string;
  price: number;
  qty: number;
  image: string;
}

const STATES = [
  'Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat',
  'Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh',
  'Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab',
  'Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh',
  'Uttarakhand','West Bengal','Delhi','Jammu & Kashmir','Ladakh',
];

type PaymentMethod = 'card' | 'upi' | 'cod';
type Step = 'shipping' | 'payment' | 'review';

/* ── Reusable wiper button classes ── */
// Dark wiper (black bg → #2e2e2e on hover)
const wiperDark = "relative overflow-hidden group bg-black text-white font-glacial transition-colors duration-300 hover:bg-[#2e2e2e]";
// Light wiper (white/transparent bg → black bg on hover)
const wiperLight = "relative overflow-hidden group border border-black text-black font-glacial transition-colors duration-300 hover:text-white";

function WiperDark({ children, className = '', ...props }: React.ButtonHTMLAttributes<HTMLButtonElement> & { children: React.ReactNode }) {
  return (
    <button className={`${wiperDark} ${className}`} {...props}>
      <span className="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-out bg-[#2e2e2e]" />
      {children}
    </button>
  );
}

function WiperLight({ children, className = '', ...props }: React.ButtonHTMLAttributes<HTMLButtonElement> & { children: React.ReactNode }) {
  return (
    <button className={`${wiperLight} ${className}`} {...props}>
      <span className="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-out bg-black" />
      {children}
    </button>
  );
}

export default function CheckOutPage() {
  const [items, setItems] = useState<CartItem[]>([]);
  const [step, setStep] = useState<Step>('shipping');
  const [orderPlaced, setOrderPlaced] = useState(false);
  const [summaryOpen, setSummaryOpen] = useState(false);
  const [promoCode, setPromoCode] = useState('');
  const [promoApplied, setPromoApplied] = useState(false);
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('card');
  const [placing, setPlacing] = useState(false);

  const [shipping, setShipping] = useState({
    firstName: '', lastName: '', email: '', phone: '',
    address: '', apartment: '', city: '', state: '', pincode: '',
  });
  const [shippingErrors, setShippingErrors] = useState<Partial<typeof shipping>>({});
  const [card, setCard] = useState({ number: '', name: '', expiry: '', cvv: '' });
  const [upi, setUpi] = useState('');
  const [cardErrors, setCardErrors] = useState<Partial<typeof card>>({});

  useEffect(() => {
    const checkoutNow = localStorage.getItem('checkout_now');
    const savedCart = localStorage.getItem('cart');
    
    const dataToLoad = checkoutNow || savedCart;

    if (dataToLoad) {
      try {
        const data = JSON.parse(dataToLoad);
        setItems(data.map((item: any, i: number) => ({
          id: item.id || item.name || i,
          name: item.name,
          category: item.category || 'Collection',
          size: item.size || '100ml EDP',
          price: item.price || 4200,
          qty: Number(item.qty || 1),
          image: item.image,
        })));
      } catch {}
    }
  }, []);

  const subtotal = items.reduce((acc, item) => acc + item.price * item.qty, 0);
  const discount = promoApplied ? Math.round(subtotal * 0.1) : 0;
  const shippingCost = subtotal > 5000 || items.length === 0 ? 0 : 299;
  const total = subtotal - discount + shippingCost;

  const applyPromo = () => {
    if (promoCode.trim().toLowerCase() === 'gray10') setPromoApplied(true);
  };

  const formatCard = (val: string) =>
    val.replace(/\D/g, '').slice(0, 16).replace(/(.{4})/g, '$1 ').trim();

  const formatExpiry = (val: string) => {
    const digits = val.replace(/\D/g, '').slice(0, 4);
    if (digits.length >= 3) return digits.slice(0, 2) + '/' + digits.slice(2);
    return digits;
  };

  const validateShipping = () => {
    const errors: Partial<typeof shipping> = {};
    if (!shipping.firstName.trim()) errors.firstName = 'Required';
    if (!shipping.lastName.trim()) errors.lastName = 'Required';
    if (!shipping.email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) errors.email = 'Invalid email';
    if (!shipping.phone.match(/^\d{10}$/)) errors.phone = '10 digits required';
    if (!shipping.address.trim()) errors.address = 'Required';
    if (!shipping.city.trim()) errors.city = 'Required';
    if (!shipping.state) errors.state = 'Required';
    if (!shipping.pincode.match(/^\d{6}$/)) errors.pincode = '6 digits required';
    setShippingErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const validateCard = () => {
    const errors: Partial<typeof card> = {};
    if (card.number.replace(/\s/g, '').length < 16) errors.number = 'Invalid card number';
    if (!card.name.trim()) errors.name = 'Required';
    if (!card.expiry.match(/^\d{2}\/\d{2}$/)) errors.expiry = 'MM/YY required';
    if (card.cvv.length < 3) errors.cvv = 'Invalid CVV';
    setCardErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleShippingNext = () => { if (validateShipping()) setStep('payment'); };
  const handlePaymentNext = () => { if (paymentMethod === 'card' && !validateCard()) return; setStep('review'); };

  const handlePlaceOrder = async () => {
    setPlacing(true);
    await new Promise(r => setTimeout(r, 1800));
    localStorage.removeItem('cart');
    localStorage.removeItem('checkout_now'); 
    window.dispatchEvent(new Event('cartUpdated'));
    setOrderPlaced(true);
    setPlacing(false);
  };

  const steps: { key: Step; label: string }[] = [
    { key: 'shipping', label: 'Shipping' },
    { key: 'payment', label: 'Payment' },
    { key: 'review', label: 'Review' },
  ];
  const stepIndex = steps.findIndex(s => s.key === step);

  // ── ORDER PLACED ──
  if (orderPlaced) {
    return (
      <div className="min-h-screen bg-[#F9F6F1] flex items-center justify-center px-4">
        <motion.div
          initial={{ opacity: 0, scale: 0.95, y: 20 }}
          animate={{ opacity: 1, scale: 1, y: 0 }}
          transition={{ duration: 0.6, ease: 'easeOut' }}
          className="text-center max-w-md"
        >
          <div className="w-20 h-20 rounded-full bg-black flex items-center justify-center mx-auto mb-8">
            <Check className="w-9 h-9 text-white" strokeWidth={1.5} />
          </div>
          <p className="text-[10px] tracking-[0.5em] uppercase font-glacial text-black/50 mb-3">Order Confirmed</p>
          <h1 className="text-4xl font-light font-qlassy text-[#1a1a1a] mb-4 tracking-tight">Thank You</h1>
          <p className="text-[14px] font-glacial text-black/60 leading-relaxed mb-10">
            Your order has been placed successfully. A confirmation will be sent to <strong>{shipping.email || 'your email'}</strong>.
          </p>
          {/* Wiper Link */}
          <Link
            href="/"
            className="inline-flex items-center gap-3 px-8 py-4 bg-black text-white text-[11px] tracking-[0.35em] uppercase font-glacial relative overflow-hidden group hover:text-white transition-colors duration-300"
          >
            <span className="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-out bg-[#2e2e2e]" />
            <span className="relative z-10">Back to Home</span>
            <ArrowRight className="relative z-10 w-3.5 h-3.5 group-hover:translate-x-1 transition-transform duration-300" />
          </Link>
        </motion.div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[#F9F6F1]">
      <div
        className="fixed top-0 right-0 w-[500px] h-[500px] rounded-full pointer-events-none z-0"
        style={{ background: 'radial-gradient(circle, rgba(180,155,110,0.07) 0%, transparent 70%)', transform: 'translate(30%, -30%)' }}
      />

      <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 pt-28 pb-20">

        {/* Back to Cart — wiper Link */}
        <Link
          href="/cart"
          className="inline-flex items-center gap-2 text-[11px] tracking-[0.35em] uppercase font-glacial mb-12 group relative overflow-hidden"
          style={{ color: 'rgba(26,26,26,0.55)' }}
          onMouseEnter={(e) => ((e.currentTarget as HTMLAnchorElement).style.color = '#1a1a1a')}
          onMouseLeave={(e) => ((e.currentTarget as HTMLAnchorElement).style.color = 'rgba(26,26,26,0.55)')}
        >
          <ArrowLeft className="w-3.5 h-3.5 group-hover:-translate-x-1 transition-transform duration-300" />
          Back to Cart
        </Link>

        {/* Header */}
        <div className="flex items-end justify-between mb-10 pb-8 border-b border-black/10">
          <div>
            <p className="text-[10px] tracking-[0.5em] uppercase font-glacial text-black/50 mb-2">Secure Checkout</p>
            <h1 className="text-4xl sm:text-5xl font-light tracking-tight font-qlassy text-[#1a1a1a]">Checkout</h1>
          </div>
          <div className="hidden sm:flex items-center gap-1 text-black/30">
            <Lock className="w-3.5 h-3.5" />
            <span className="text-[10px] tracking-[0.3em] uppercase font-glacial">Encrypted</span>
          </div>
        </div>

        {/* Step Indicator */}
        <div className="flex items-center gap-0 mb-12">
          {steps.map((s, i) => (
            <div key={s.key} className="flex items-center">
              <button
                onClick={() => i < stepIndex && setStep(s.key)}
                className={`flex items-center gap-2.5 text-[10px] tracking-[0.35em] uppercase font-glacial transition-colors duration-300 ${
                  i <= stepIndex ? 'text-black' : 'text-black/30 cursor-default'
                }`}
              >
                <span className={`w-6 h-6 rounded-full flex items-center justify-center text-[10px] border transition-all duration-300 ${
                  i < stepIndex ? 'bg-black border-black text-white'
                  : i === stepIndex ? 'border-black text-black'
                  : 'border-black/20 text-black/30'
                }`}>
                  {i < stepIndex ? <Check className="w-3 h-3" strokeWidth={2.5} /> : i + 1}
                </span>
                <span className="hidden sm:block">{s.label}</span>
              </button>
              {i < steps.length - 1 && (
                <div className={`w-12 sm:w-20 h-px mx-3 transition-colors duration-500 ${i < stepIndex ? 'bg-black' : 'bg-black/15'}`} />
              )}
            </div>
          ))}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-10 xl:gap-14">

          {/* ── LEFT PANEL ── */}
          <div>
            <AnimatePresence mode="wait">

              {/* STEP 1: SHIPPING */}
              {step === 'shipping' && (
                <motion.div key="shipping" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }} transition={{ duration: 0.3 }}>
                  <p className="text-[10px] tracking-[0.5em] uppercase font-glacial text-black/50 mb-6 flex items-center gap-2">
                    <Truck className="w-3.5 h-3.5" /> Shipping Information
                  </p>
                  <div className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                      <Field label="First Name" error={shippingErrors.firstName}>
                        <input value={shipping.firstName} onChange={e => setShipping(p => ({ ...p, firstName: e.target.value }))} placeholder="Enter First Name" className="input-base" />
                      </Field>
                      <Field label="Last Name" error={shippingErrors.lastName}>
                        <input value={shipping.lastName} onChange={e => setShipping(p => ({ ...p, lastName: e.target.value }))} placeholder="Enter Last Name" className="input-base" />
                      </Field>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <Field label="Email" error={shippingErrors.email}>
                        <input type="email" value={shipping.email} onChange={e => setShipping(p => ({ ...p, email: e.target.value }))} placeholder="Enter Email" className="input-base" />
                      </Field>
                      <Field label="Phone" error={shippingErrors.phone}>
                        <input type="tel" value={shipping.phone} onChange={e => setShipping(p => ({ ...p, phone: e.target.value.replace(/\D/g, '').slice(0, 10) }))} placeholder="0000000000" className="input-base" />
                      </Field>
                    </div>
                    <Field label="Street Address" error={shippingErrors.address}>
                      <input value={shipping.address} onChange={e => setShipping(p => ({ ...p, address: e.target.value }))} placeholder="Enter Address" className="input-base" />
                    </Field>
                    <Field label="Apartment / Floor (optional)">
                      <input value={shipping.apartment} onChange={e => setShipping(p => ({ ...p, apartment: e.target.value }))} placeholder="Enter Apt" className="input-base" />
                    </Field>
                    <div className="grid grid-cols-3 gap-4">
                      <Field label="City" error={shippingErrors.city}>
                        <input value={shipping.city} onChange={e => setShipping(p => ({ ...p, city: e.target.value }))} placeholder="Enter City" className="input-base" />
                      </Field>
                      <Field label="State" error={shippingErrors.state}>
                        <select value={shipping.state} onChange={e => setShipping(p => ({ ...p, state: e.target.value }))} className="input-base bg-transparent appearance-none">
                          <option value="">Select</option>
                          {STATES.map(s => <option key={s} value={s}>{s}</option>)}
                        </select>
                      </Field>
                      <Field label="Pincode" error={shippingErrors.pincode}>
                        <input value={shipping.pincode} onChange={e => setShipping(p => ({ ...p, pincode: e.target.value.replace(/\D/g, '').slice(0, 6) }))} placeholder="000000" className="input-base" />
                      </Field>
                    </div>
                  </div>

                  {/* Continue to Payment — wiper dark */}
                  <WiperDark
                    onClick={handleShippingNext}
                    className="mt-8 w-full py-4 text-[12px] tracking-[0.4em] uppercase flex items-center justify-between px-6"
                  >
                    <span className="relative z-10">Continue to Payment</span>
                    <ArrowRight className="relative z-10 w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" />
                  </WiperDark>
                </motion.div>
              )}

              {/* STEP 2: PAYMENT */}
              {step === 'payment' && (
                <motion.div key="payment" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }} transition={{ duration: 0.3 }}>
                  <p className="text-[10px] tracking-[0.5em] uppercase font-glacial text-black/50 mb-6">Payment Method</p>

                  {/* Method selector — wiper effect on each */}
                  <div className="grid grid-cols-3 gap-3 mb-8">
                    {([
                      { key: 'card', label: 'Card', icon: CreditCard },
                      { key: 'upi', label: 'UPI', icon: Smartphone },
                      { key: 'cod', label: 'Cash on Delivery', icon: Package },
                    ] as { key: PaymentMethod; label: string; icon: any }[]).map(m => (
                      <button
                        key={m.key}
                        onClick={() => setPaymentMethod(m.key)}
                        className={`relative overflow-hidden group flex flex-col items-center gap-2 py-4 px-3 border text-[10px] tracking-[0.25em] uppercase font-glacial transition-all duration-300 ${
                          paymentMethod === m.key
                            ? 'bg-black text-white border-black'
                            : 'border-black/15 text-black/60 bg-white hover:text-white hover:border-black'
                        }`}
                      >
                        {paymentMethod !== m.key && (
                          <span className="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-out bg-black" />
                        )}
                        <m.icon className="relative z-10 w-5 h-5" strokeWidth={1.5} />
                        <span className="relative z-10 hidden sm:block text-center leading-tight">{m.label}</span>
                        <span className="relative z-10 sm:hidden">{m.key.toUpperCase()}</span>
                      </button>
                    ))}
                  </div>

                  <AnimatePresence mode="wait">
                    {paymentMethod === 'card' && (
                      <motion.div key="card" initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -10 }} className="space-y-4">
                        <Field label="Card Number" error={cardErrors.number}>
                          <input value={card.number} onChange={e => setCard(p => ({ ...p, number: formatCard(e.target.value) }))} placeholder="0000 0000 0000 0000" className="input-base tracking-widest" maxLength={19} />
                        </Field>
                        <Field label="Cardholder Name" error={cardErrors.name}>
                          <input value={card.name} onChange={e => setCard(p => ({ ...p, name: e.target.value }))} placeholder="Enter Name" className="input-base" />
                        </Field>
                        <div className="grid grid-cols-2 gap-4">
                          <Field label="Expiry" error={cardErrors.expiry}>
                            <input value={card.expiry} onChange={e => setCard(p => ({ ...p, expiry: formatExpiry(e.target.value) }))} placeholder="MM/YY" className="input-base" maxLength={5} />
                          </Field>
                          <Field label="CVV" error={cardErrors.cvv}>
                            <input type="password" value={card.cvv} onChange={e => setCard(p => ({ ...p, cvv: e.target.value.replace(/\D/g, '').slice(0, 4) }))} placeholder="•••" className="input-base" maxLength={4} />
                          </Field>
                        </div>
                      </motion.div>
                    )}
                    {paymentMethod === 'upi' && (
                      <motion.div key="upi" initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -10 }}>
                        <Field label="UPI ID">
                          <input value={upi} onChange={e => setUpi(e.target.value)} placeholder="yourname@upi" className="input-base" />
                        </Field>
                        <p className="text-[10px] tracking-[0.2em] font-glacial text-black/40 mt-3">Accepts: GPay, PhonePe, Paytm, BHIM and all UPI apps</p>
                      </motion.div>
                    )}
                    {paymentMethod === 'cod' && (
                      <motion.div key="cod" initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -10 }} className="p-5 border border-black/10 bg-white rounded-sm">
                        <div className="flex items-start gap-4">
                          <Package className="w-5 h-5 text-black/40 mt-0.5 flex-shrink-0" strokeWidth={1.5} />
                          <div>
                            <p className="text-[13px] font-glacial text-black/80 mb-1">Cash on Delivery</p>
                            <p className="text-[11px] font-glacial text-black/50 leading-relaxed tracking-wide">Pay in cash when your order arrives. An additional ₹49 COD handling fee may apply.</p>
                          </div>
                        </div>
                      </motion.div>
                    )}
                  </AnimatePresence>

                  <div className="flex gap-3 mt-8">
                    {/* Back — wiper light */}
                    <WiperLight
                      onClick={() => setStep('shipping')}
                      className="py-4 px-6 text-[11px] tracking-[0.3em] uppercase flex items-center gap-2"
                    >
                      <ArrowLeft className="relative z-10 w-3.5 h-3.5 group-hover:-translate-x-1 transition-transform duration-300" />
                      <span className="relative z-10 hidden sm:block">Back</span>
                    </WiperLight>
                    {/* Review Order — wiper dark */}
                    <WiperDark
                      onClick={handlePaymentNext}
                      className="flex-1 py-4 text-[12px] tracking-[0.4em] uppercase flex items-center justify-between px-6"
                    >
                      <span className="relative z-10">Review Order</span>
                      <ArrowRight className="relative z-10 w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" />
                    </WiperDark>
                  </div>
                </motion.div>
              )}

              {/* STEP 3: REVIEW */}
              {step === 'review' && (
                <motion.div key="review" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }} transition={{ duration: 0.3 }} className="space-y-6">
                  <ReviewBlock title="Shipping To" onEdit={() => setStep('shipping')}>
                    <p className="text-[14px] font-glacial text-black/80">{shipping.firstName} {shipping.lastName}</p>
                    <p className="text-[13px] font-glacial text-black/55 mt-1">{shipping.address}{shipping.apartment ? `, ${shipping.apartment}` : ''}, {shipping.city}, {shipping.state} — {shipping.pincode}</p>
                    <p className="text-[13px] font-glacial text-black/55">{shipping.email} · {shipping.phone}</p>
                  </ReviewBlock>

                  <ReviewBlock title="Payment" onEdit={() => setStep('payment')}>
                    {paymentMethod === 'card' && (
                      <p className="text-[14px] font-glacial text-black/80 flex items-center gap-2">
                        <CreditCard className="w-4 h-4" strokeWidth={1.5} />
                        Card ending in {card.number.replace(/\s/g, '').slice(-4) || '****'}
                      </p>
                    )}
                    {paymentMethod === 'upi' && (
                      <p className="text-[14px] font-glacial text-black/80 flex items-center gap-2">
                        <Smartphone className="w-4 h-4" strokeWidth={1.5} />
                        UPI — {upi || 'yourname@upi'}
                      </p>
                    )}
                    {paymentMethod === 'cod' && (
                      <p className="text-[14px] font-glacial text-black/80 flex items-center gap-2">
                        <Package className="w-4 h-4" strokeWidth={1.5} />
                        Cash on Delivery
                      </p>
                    )}
                  </ReviewBlock>

                  <div className="p-5 bg-white border border-black/10 rounded-sm">
                    <p className="text-[10px] tracking-[0.45em] uppercase font-glacial text-black/50 mb-4">{items.length} Items</p>
                    <div className="space-y-4">
                      {items.map(item => (
                        <div key={item.id} className="flex items-center gap-4">
                          <div className="relative w-12 h-12 bg-[#F9F6F1] border border-black/8 flex-shrink-0">
                            <Image src={item.image} alt={item.name} fill className="object-contain p-1" />
                          </div>
                          <div className="flex-1 min-w-0">
                            <p className="text-[13px] font-glacial text-black/80 truncate">{item.name}</p>
                            <p className="text-[11px] font-glacial text-black/45">{item.size} · Qty {item.qty}</p>
                          </div>
                          <p className="text-[14px] font-glacial text-black/80 flex-shrink-0">₹{(item.price * item.qty).toLocaleString('en-IN')}</p>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div className="flex gap-3">
                    {/* Back — wiper light */}
                    <WiperLight
                      onClick={() => setStep('payment')}
                      className="py-4 px-6 text-[11px] tracking-[0.3em] uppercase flex items-center gap-2"
                    >
                      <ArrowLeft className="relative z-10 w-3.5 h-3.5 group-hover:-translate-x-1 transition-transform duration-300" />
                      <span className="relative z-10 hidden sm:block">Back</span>
                    </WiperLight>
                    {/* Place Order — wiper dark */}
                    <WiperDark
                      onClick={handlePlaceOrder}
                      disabled={placing}
                      className="flex-1 py-4 text-[12px] tracking-[0.4em] uppercase flex items-center justify-between px-6 disabled:opacity-60"
                    >
                      <span className="relative z-10">{placing ? 'Placing Order...' : 'Place Order'}</span>
                      {placing ? (
                        <svg className="relative z-10 w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                      ) : (
                        <ArrowRight className="relative z-10 w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" />
                      )}
                    </WiperDark>
                  </div>
                </motion.div>
              )}
            </AnimatePresence>
          </div>

          {/* ── RIGHT: ORDER SUMMARY ── */}
          <div className="lg:sticky lg:top-28 self-start">
            {/* Mobile toggle */}
            <button
              className="lg:hidden w-full flex items-center justify-between py-4 px-5 bg-white border border-black/10 mb-3 font-glacial"
              onClick={() => setSummaryOpen(o => !o)}
            >
              <span className="text-[11px] tracking-[0.35em] uppercase text-black/60">Order Summary</span>
              <div className="flex items-center gap-3">
                <span className="text-[15px] font-light">₹{total.toLocaleString('en-IN')}</span>
                {summaryOpen ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
              </div>
            </button>

            <div className={`lg:block ${summaryOpen ? 'block' : 'hidden'}`}>
              <div className="rounded-sm p-7 bg-white border border-black/10">
                <p className="text-[10px] tracking-[0.5em] uppercase font-glacial text-black/50 mb-5">Order Summary</p>

                <div className="space-y-3 mb-5">
                  {items.map(item => (
                    <div key={item.id} className="flex items-center gap-3">
                      {/* FIX: Removed 'overflow-hidden' from here to prevent the badge from clipping */}
                      <div className="relative w-10 h-10 bg-[#F9F6F1] border border-black/8 flex-shrink-0">
                        <Image src={item.image} alt={item.name} fill className="object-contain p-1" />
                        {/* FIX: Added 'z-10' and adjusted the negative positioning slightly if needed, though mostly removing overflow-hidden works */}
                        <span className="absolute -top-1.5 -right-1.5 w-4 h-4 bg-black text-white text-[9px] rounded-full flex items-center justify-center font-glacial z-10">{item.qty}</span>
                      </div>
                      <div className="flex-1 min-w-0 ml-1">
                        <p className="text-[12px] font-glacial text-black/75 truncate">{item.name}</p>
                        <p className="text-[10px] font-glacial text-black/40">{item.size}</p>
                      </div>
                      <p className="text-[13px] font-glacial text-black/70 flex-shrink-0">₹{(item.price * item.qty).toLocaleString('en-IN')}</p>
                    </div>
                  ))}
                </div>

                <div className="h-px bg-black/8 mb-5" />

                {/* Promo */}
                <div className="mb-5">
                  <p className="text-[9px] tracking-[0.4em] uppercase font-glacial text-black/50 mb-2">Promo Code</p>
                  <div className="flex gap-2">
                    <input
                      type="text" value={promoCode} onChange={e => setPromoCode(e.target.value)}
                      placeholder="GRAY10" disabled={promoApplied}
                      onKeyDown={e => e.key === 'Enter' && applyPromo()}
                      className="flex-1 bg-transparent px-3 py-2 text-[11px] tracking-[0.15em] font-glacial border border-black/15 focus:outline-none focus:border-black/40 transition-colors placeholder:text-black/25"
                    />
                    {/* Apply — wiper dark */}
                    <button
                      onClick={applyPromo}
                      disabled={promoApplied || !promoCode.trim()}
                      className="relative overflow-hidden group px-4 py-2 bg-black text-white text-[9px] tracking-[0.3em] uppercase font-glacial disabled:opacity-40"
                    >
                      <span className="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-out bg-[#2e2e2e]" />
                      <span className="relative z-10">{promoApplied ? 'Applied' : 'Apply'}</span>
                    </button>
                  </div>
                  {promoApplied
                    ? <p className="text-[10px] font-glacial text-green-700 mt-1.5 tracking-wide">✦ GRAY10 — 10% off applied</p>
                    : <p className="text-[10px] font-glacial text-black/35 mt-1.5">Try: GRAY10</p>
                  }
                </div>

                <div className="h-px bg-black/8 mb-5" />

                <div className="space-y-3 text-[14px] font-glacial mb-5">
                  <div className="flex justify-between text-black/65"><span>Subtotal</span><span>₹{subtotal.toLocaleString('en-IN')}</span></div>
                  {promoApplied && <div className="flex justify-between text-green-700"><span>Discount (10%)</span><span>−₹{discount.toLocaleString('en-IN')}</span></div>}
                  <div className="flex justify-between text-black/65">
                    <span>Shipping</span>
                    <span>{shippingCost === 0 ? <span className="text-green-700">Free</span> : `₹${shippingCost}`}</span>
                  </div>
                </div>

                <div className="h-px bg-black/8 mb-4" />

                <div className="flex justify-between items-baseline">
                  <p className="text-[10px] tracking-[0.4em] uppercase font-glacial text-black/50">Total</p>
                  <p className="text-2xl font-light font-glacial text-black">₹{total.toLocaleString('en-IN')}</p>
                </div>

                <p className="text-center text-[9px] tracking-[0.3em] uppercase font-glacial text-black/35 mt-5 flex items-center justify-center gap-1.5">
                  <Lock className="w-3 h-3" /> Secured & Encrypted
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <style jsx global>{`
        .input-base {
          width: 100%;
          background: white;
          border: 1px solid rgba(26,26,26,0.15);
          padding: 12px 14px;
          font-size: 13px;
          font-family: var(--font-glacial, sans-serif);
          color: #1a1a1a;
          outline: none;
          transition: border-color 0.2s;
        }
        .input-base:focus { border-color: rgba(26,26,26,0.5); }
        .input-base::placeholder { color: rgba(26,26,26,0.3); }
        select.input-base option { background: white; color: #1a1a1a; }
      `}</style>
    </div>
  );
}

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
  return (
    <div>
      <label
        className="font-glacial"
        style={{
          display: 'block',
          fontSize: '10px',
          letterSpacing: '0.35em',
          textTransform: 'uppercase',
          color: 'rgba(26,26,26,0.75)',
          marginBottom: '6px',
          fontWeight: 600,
        }}
      >
        {label}
      </label>
      {children}
      {error && <p className="text-[10px] font-glacial text-red-500 mt-1">{error}</p>}
    </div>
  );
}

function ReviewBlock({ title, onEdit, children }: { title: string; onEdit: () => void; children: React.ReactNode }) {
  return (
    <div className="p-5 bg-white border border-black/10 rounded-sm">
      <div className="flex items-center justify-between mb-3">
        <p className="text-[10px] tracking-[0.45em] uppercase font-glacial text-black/50">{title}</p>
        {/* Edit — wiper light inline */}
        <button
          onClick={onEdit}
          className="relative overflow-hidden group text-[10px] tracking-[0.3em] uppercase font-glacial text-black/40 hover:text-white transition-colors duration-300 border-b border-black/20 hover:border-black pb-0.5 px-2 py-0.5"
        >
          <span className="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-out bg-black" />
          <span className="relative z-10">Edit</span>
        </button>
      </div>
      {children}
    </div>
  );
}