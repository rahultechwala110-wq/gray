"use client";
import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X } from 'lucide-react';
import Image from 'next/image';
import { useRouter } from 'next/navigation';

// ─── Session helpers (export করো parent-এ use করতে) ───────────────────────
export const hasPerfumeFinderBeenShown = (): boolean =>
  typeof window !== 'undefined' && sessionStorage.getItem('pf_shown') === '1';

export const markPerfumeFinderShown = (): void => {
  if (typeof window !== 'undefined') sessionStorage.setItem('pf_shown', '1');
};
// ───────────────────────────────────────────────────────────────────────────

const FRAGRANCE_DATA = [
  { gender: 'MALE',   name: 'BRAVE',      noteSegment: 'Warm Amber & Vanilla',   impression: 'Warm and comforting',       format: 'liquid',
    image: '/products/brave-men.png',      identity: 'The Fearless Charmer',      story: 'Courage is rarely loud. Sometimes it is simply the confidence to be yourself.',
    url: '/brave-mens-perfume-55ml' },
  { gender: 'MALE',   name: 'BOSS',       noteSegment: 'Spicy & Aromatic',       impression: 'Bold and unforgettable',    format: 'liquid',
    image: '/products/boss-men.png',       identity: 'The Power Strategist',      story: 'True authority is never announced—it is simply felt.',
    url: '/boss-mens-perfume-55m' },
  { gender: 'MALE',   name: 'GENTLE',     noteSegment: 'Fresh Citrus & Aquatic', impression: 'Fresh and uplifting',       format: 'liquid',
    image: '/products/gentle-men.png',     identity: 'The Quiet Gentleman',       story: 'Elegance is often found in the softest gestures.',
    url: '/gentle-mens-perfume-55ml' },
  { gender: 'MALE',   name: 'BOLD',       noteSegment: 'Spicy & Aromatic',       impression: 'Bold and unforgettable',    format: 'liquid',
    image: '/products/bold-men.png',       identity: 'The Untamed Force',         story: 'Some energies are simply impossible to contain.',
    url: '/gold-men-perfume-55ml' },
  { gender: 'MALE',   name: 'GENEROUS',   noteSegment: 'Warm Amber & Vanilla',   impression: 'Warm and comforting',       format: 'liquid',
    image: '/products/generous.png',       identity: 'The Warm-Hearted Leader',   story: 'True greatness lies in what you give to others.',
    url: '/generous-mens-perfume-55ml' },
  { gender: 'MALE',   name: 'GROOMED',    noteSegment: 'Woody & Earthy',         impression: 'Classic and sophisticated', format: 'liquid',
    image: '/products/groomed.png',        identity: 'The Refined Traditionalist',story: 'Timeless style never fades.',
    url: '/groomed-mens-perfume-55m' },
  { gender: 'FEMALE', name: 'BLISS',      noteSegment: 'Fresh Citrus & Aquatic', impression: 'Fresh and uplifting',       format: 'liquid',
    image: '/products/bliss-woman.png',    identity: 'The Radiant Optimist',      story: 'Happiness is a fragrance that cannot be contained.',
    url: '/bliss-womens-perfume-55ml' },
  { gender: 'FEMALE', name: 'GORGEOUS',   noteSegment: 'Floral & Soft Musk',     impression: 'Elegant and graceful',      format: 'liquid',
    image: '/products/gorgeous-woman.png', identity: 'The Effortless Muse',       story: 'True beauty never tries too hard.',
    url: '/gorgeous-womens-perfume-55ml' },
  { gender: 'FEMALE', name: 'BRAVEHEART', noteSegment: 'Fresh Citrus & Aquatic', impression: 'Fresh and uplifting',       format: 'liquid',
    image: '/products/braveheart-woman.png',identity: 'The Free Spirit',          story: 'Freedom has its own scent.',
    url: '/braveheart-womens-perfume-55ml' },
  { gender: 'FEMALE', name: 'GLORIOUS',   noteSegment: 'Oud & Smoky Resins',     impression: 'Deep and intriguing',       format: 'liquid',
    image: '/products/glorious-woman.png', identity: 'The Regal Presence',        story: 'Some presences feel timeless.',
    url: '/glorious-womens-perfume-55ml' },
  { gender: 'FEMALE', name: 'GIFTED',     noteSegment: 'Spicy & Aromatic',       impression: 'Bold and unforgettable',    format: 'liquid',
    image: '/products/gifted-woman.png',   identity: 'The Enigmatic Mind',        story: 'Mystery is the most powerful allure.',
    url: '/gifted-womens-perfume-55ml' },
  { gender: 'FEMALE', name: 'BRILLIANCE', noteSegment: 'Floral & Soft Musk',     impression: 'Elegant and graceful',      format: 'liquid',
    image: '/products/brilliance-woman.png',identity: 'The Luminous Icon',        story: 'Some people simply shine.',
    url: '/brilliance-womens-perfume-55ml' },
  { gender: 'UNISEX', name: 'B612',       noteSegment: 'Fresh Citrus & Aquatic', impression: 'Fresh and uplifting',       format: 'solid',
    image: '/products/b612.png',           identity: 'The Cosmic Dreamer',        story: 'Imagination has no boundaries.',
    url: '/b612' },
  { gender: 'UNISEX', name: 'BULGE',      noteSegment: 'Spicy & Aromatic',       impression: 'Bold and unforgettable',    format: 'solid',
    image: '/products/bulge.png',          identity: 'The Magnetic Rebel',        story: 'Rules were never meant to define you.',
    url: '/bulge' },
  { gender: 'UNISEX', name: 'BRAHE',      noteSegment: 'Woody & Earthy',         impression: 'Classic and sophisticated', format: 'solid',
    image: '/products/brahe.png',          identity: 'The Thoughtful Visionary',  story: 'Vision begins with curiosity.',
    url: '/brahe' },
  { gender: 'UNISEX', name: 'GLIESE',     noteSegment: 'Oud & Smoky Resins',     impression: 'Deep and intriguing',       format: 'solid',
    image: '/products/gliese.png',         identity: 'The Dark Voyager',          story: 'Mystery is where discovery begins.',
    url: '/gliese' },
  { gender: 'UNISEX', name: 'GANYMEDE',   noteSegment: 'Spicy & Aromatic',       impression: 'Bold and unforgettable',    format: 'solid',
    image: '/products/ganymede.png',       identity: 'The Celestial Pioneer',     story: 'The future belongs to explorers.',
    url: '/ganymede' },
  { gender: 'UNISEX', name: 'GASPRA',     noteSegment: 'Oud & Smoky Resins',     impression: 'Deep and intriguing',       format: 'solid',
    image: '/products/gaspra.png',         identity: 'The Ancient Sovereign',     story: 'Power can feel timeless.',
    url: '/gaspra' },
];

const RECOMMENDATIONS = {
  liquid: {
    BRAVE: { primary: 'BRAVE', secondary: 'BOSS' }, BOSS: { primary: 'BOSS', secondary: 'BOLD' },
    GENTLE: { primary: 'GENTLE', secondary: 'GROOMED' }, BOLD: { primary: 'BOLD', secondary: 'BOSS' },
    GENEROUS: { primary: 'GENEROUS', secondary: 'BRAVE' }, GROOMED: { primary: 'GROOMED', secondary: 'GENTLE' },
    BLISS: { primary: 'BLISS', secondary: 'GORGEOUS' }, GORGEOUS: { primary: 'GORGEOUS', secondary: 'BRILLIANCE' },
    BRAVEHEART: { primary: 'BRAVEHEART', secondary: 'BLISS' }, GLORIOUS: { primary: 'GLORIOUS', secondary: 'GIFTED' },
    GIFTED: { primary: 'GIFTED', secondary: 'GLORIOUS' }, BRILLIANCE: { primary: 'BRILLIANCE', secondary: 'GORGEOUS' },
  },
  solid: {
    B612: { primary: 'B612', secondary: 'BRAHE' }, BULGE: { primary: 'BULGE', secondary: 'GANYMEDE' },
    BRAHE: { primary: 'BRAHE', secondary: 'B612' }, GLIESE: { primary: 'GLIESE', secondary: 'GASPRA' },
    GANYMEDE: { primary: 'GANYMEDE', secondary: 'BULGE' }, GASPRA: { primary: 'GASPRA', secondary: 'GLIESE' },
  },
  both: {
    BRAVE: { primary: 'BRAVE', secondary: 'BULGE' }, BOSS: { primary: 'BOSS', secondary: 'GANYMEDE' },
    GENTLE: { primary: 'GENTLE', secondary: 'B612' }, BOLD: { primary: 'BOLD', secondary: 'BULGE' },
    GENEROUS: { primary: 'GENEROUS', secondary: 'BRAHE' }, GROOMED: { primary: 'GROOMED', secondary: 'BRAHE' },
    BLISS: { primary: 'BLISS', secondary: 'B612' }, GORGEOUS: { primary: 'GORGEOUS', secondary: 'BRAHE' },
    BRAVEHEART: { primary: 'BRAVEHEART', secondary: 'GANYMEDE' }, GLORIOUS: { primary: 'GLORIOUS', secondary: 'GLIESE' },
    GIFTED: { primary: 'GIFTED', secondary: 'GASPRA' }, BRILLIANCE: { primary: 'BRILLIANCE', secondary: 'B612' },
  },
};

const GENDER_OPTIONS = [
  { value: 'MALE',   label: 'For Him', sub: 'Bold, grounded & refined scents', image: '/products/brave-men.png'       },
  { value: 'FEMALE', label: 'For Her', sub: 'Floral, fresh & luminous scents', image: '/products/bliss-woman.png'     },
  { value: 'UNISEX', label: 'Shared',  sub: 'Boundary-free, universal scents', image: '/products/b612.png'            },
];

const FORMAT_OPTIONS = [
  { value: 'liquid', label: 'Liquid Spray', sub: 'The classic spray on pulse points',  image: '/products/brave-men.png'   },
  { value: 'solid',  label: 'Solid Scent',  sub: 'Applied directly on skin, portable', image: '/products/b612.png'        },
  { value: 'both',   label: 'Open to Both', sub: 'Discover the full collection',        image: '/products/boss-men.png'    },
];

const NOTE_SEGMENTS = [
  { value: 'Warm Amber & Vanilla',   label: 'Warm Amber & Vanilla',   desc: 'Amber, vanilla and honey warmth — rich and enveloping', image: '/products/brave-men.png'       },
  { value: 'Spicy & Aromatic',       label: 'Spicy & Aromatic',       desc: 'Citrus with amber and spice — confident and bold',      image: '/products/boss-men.png'        },
  { value: 'Fresh Citrus & Aquatic', label: 'Fresh Citrus & Aquatic', desc: 'Marine freshness, coastal air, vibrant citrus',         image: '/products/gentle-men.png'      },
  { value: 'Floral & Soft Musk',     label: 'Floral & Soft Musk',     desc: 'Delicate florals layered with soft, powdery musk',      image: '/products/gorgeous-woman.png'  },
  { value: 'Woody & Earthy',         label: 'Woody & Earthy',         desc: 'Sandalwood, patchouli and deep earthy warmth',          image: '/products/groomed.png'         },
  { value: 'Oud & Smoky Resins',     label: 'Oud & Smoky Resins',     desc: 'Oud, leather and smoky amber — deep and regal',         image: '/products/glorious-woman.png'  },
];

const IMPRESSIONS = [
  { value: 'Warm and comforting',       label: 'Warm & Comforting',       image: '/products/generous.png'        },
  { value: 'Bold and unforgettable',    label: 'Bold & Unforgettable',    image: '/products/bold-men.png'        },
  { value: 'Fresh and uplifting',       label: 'Fresh & Uplifting',       image: '/products/bliss-woman.png'     },
  { value: 'Elegant and graceful',      label: 'Elegant & Graceful',      image: '/products/brilliance-woman.png'},
  { value: 'Classic and sophisticated', label: 'Classic & Sophisticated', image: '/products/groomed.png'         },
  { value: 'Deep and intriguing',       label: 'Deep & Intriguing',       image: '/products/glorious-woman.png'  },
];

const STEPS = ['Format', 'For Who', 'Aroma', 'Impression'];
const DEFAULT_IMAGE = '/popup/popup.png';

function getRecommendation(gender: string, format: string, noteSegment: string, impression: string) {
  const matches = FRAGRANCE_DATA.filter(f => {
    const genderMatch = f.gender === gender || (gender !== 'UNISEX' && f.gender === 'UNISEX');
    const formatMatch = format === 'both' ? true : f.format === format;
    return genderMatch && formatMatch && f.noteSegment === noteSegment && f.impression === impression;
  });
  if (matches.length > 0) {
    const primary = matches[0].name;
    const recTable = format === 'both' ? RECOMMENDATIONS.both : format === 'solid' ? RECOMMENDATIONS.solid : RECOMMENDATIONS.liquid;
    const rec = recTable[primary as keyof typeof recTable];
    return rec || { primary, secondary: matches[1]?.name || primary };
  }
  const fallback = FRAGRANCE_DATA.filter(f => {
    const genderMatch = f.gender === gender || f.gender === 'UNISEX';
    const formatMatch = format === 'both' ? true : f.format === format;
    return genderMatch && formatMatch && f.noteSegment === noteSegment;
  });
  if (fallback.length > 0) {
    const primary = fallback[0].name;
    const recTable = format === 'both' ? RECOMMENDATIONS.both : format === 'solid' ? RECOMMENDATIONS.solid : RECOMMENDATIONS.liquid;
    const rec = recTable[primary as keyof typeof recTable];
    return rec || { primary, secondary: fallback[1]?.name || primary };
  }
  return { primary: 'BRAVE', secondary: 'BOSS' };
}

function getFragranceInfo(name: string) {
  return FRAGRANCE_DATA.find(f => f.name === name);
}

const fade = {
  initial: { opacity: 0, x: 12 }, animate: { opacity: 1, x: 0 },
  exit: { opacity: 0, x: -12 }, transition: { duration: 0.25, ease: 'easeOut' as const },
};

interface PerfumeFinderProps { isOpen: boolean; onClose: () => void; }

const PerfumeFinder = ({ isOpen, onClose }: PerfumeFinderProps) => {
  const router = useRouter();
  const [step,        setStep]        = useState(0);
  const [gender,      setGender]      = useState('');
  const [format,      setFormat]      = useState('');
  const [noteSegment, setNoteSegment] = useState('');
  const [impression,  setImpression]  = useState('');
  const [result,      setResult]      = useState<{ primary: string; secondary: string } | null>(null);
  const [hoverImage, setHoverImage] = useState<string>(DEFAULT_IMAGE);

  useEffect(() => {
    if (isOpen) {
      // popup খুললেই session mark করো — auto বা menu যেভাবেই হোক
      // এতে পরের বার page-এ এলে parent auto-show করবে না
      markPerfumeFinderShown();
      setStep(0); setFormat(''); setGender(''); setNoteSegment(''); setImpression(''); setResult(null);
      setHoverImage(DEFAULT_IMAGE);
    }
  }, [isOpen]);

  const handleClose = () => onClose();

  if (!isOpen) return null;

  const primaryInfo   = result ? getFragranceInfo(result.primary)   : null;
  const secondaryInfo = result ? getFragranceInfo(result.secondary) : null;

  const leftImage = step === 4 && primaryInfo?.image ? primaryInfo.image : hoverImage;

  const availableNotes = Array.from(new Set(
    FRAGRANCE_DATA.filter(f => {
      const gMatch = f.gender === gender || f.gender === 'UNISEX';
      const fMatch = format === 'both' ? true : f.format === format;
      return gMatch && fMatch;
    }).map(f => f.noteSegment)
  ));
  const filteredNotes = NOTE_SEGMENTS.filter(n => availableNotes.includes(n.value));

  const selectFormat     = (v: string) => { setFormat(v);      setStep(1); setHoverImage(DEFAULT_IMAGE); };
  const selectGender     = (v: string) => { setGender(v);      setStep(2); setHoverImage(DEFAULT_IMAGE); };
  const selectNote       = (v: string) => { setNoteSegment(v); setStep(3); setHoverImage(DEFAULT_IMAGE); };
  const selectImpression = (v: string) => {
    setImpression(v);
    const res = getRecommendation(gender, format, noteSegment, v);
    setResult(res);
    const info = getFragranceInfo(res.primary);
    if (info?.image) setHoverImage(info.image);
    setStep(4);
  };

  return (
    <div className="fixed inset-0 z-[999] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
      <motion.div
        initial={{ opacity: 0, y: 24, scale: 0.97 }} animate={{ opacity: 1, y: 0, scale: 1 }}
        exit={{ opacity: 0, y: 24, scale: 0.97 }} transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
        className="relative w-full max-w-4xl bg-[#FAF8F5] overflow-hidden shadow-2xl flex items-stretch h-[75vh]"
        style={{ fontFamily: "'Cormorant Garamond', Georgia, serif", minHeight: '580px' }}
      >
        {/* ── LEFT PANEL — Hover Image ── */}
        <div className="hidden md:flex md:flex-col w-[42%] flex-shrink-0 relative bg-[#EEE8DF] overflow-hidden self-stretch">
          <div className="h-[3px] w-full bg-[#1A1612] flex-shrink-0 z-10" />
          <div className="absolute inset-0 top-[3px] flex items-center justify-center pointer-events-none select-none z-0 opacity-10">
            <div className="relative" style={{ width: '480px', height: '600px' }}>
              <Image src="/logo 2.png" alt="Gray" fill className="object-contain" />
            </div>
          </div>
          <AnimatePresence mode="wait">
            <motion.div
              key={leftImage}
              initial={{ opacity: 0, scale: 1.04 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.96 }}
              transition={{ duration: 0.35, ease: 'easeOut' }}
              className="absolute inset-0 flex items-center justify-center p-10"
            >
              <div className="relative w-full h-full">
                <Image src={leftImage} alt="" fill className="object-contain drop-shadow-2xl" />
              </div>
            </motion.div>
          </AnimatePresence>
          <p className="absolute bottom-4 left-0 right-0 text-center text-[9px] tracking-[0.3em] uppercase font-glacial text-[#B8AFA6] z-10">
            Gray Fragrance
          </p>
        </div>

        {/* ── RIGHT PANEL ── */}
        <div className="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden">
          <div className="h-[3px] w-full bg-[#1A1612] flex-shrink-0" />

          <div className="flex items-start justify-between px-8 pt-7 pb-5 border-b border-[#E8E0D5] flex-shrink-0">
            <div>
              <p className="text-[11px] tracking-[0.22em] text-[#1A1612] uppercase font-glacial mb-1">Fragrance Finder</p>
              <h2 className="text-3xl text-[#1A1612] leading-tight tracking-wide font-qlassy">
                {step < 4 ? 'Find Your Signature Scent' : 'Your Fragrance Match'}
              </h2>
            </div>
            <button onClick={handleClose} className="mt-1 text-[#B8AFA6] hover:text-[#1A1612] transition-colors">
              <X size={18} />
            </button>
          </div>

          {step < 4 && (
            <div className="flex gap-1 px-8 pt-4 flex-shrink-0">
              {STEPS.map((s, i) => (
                <div key={s} className="flex-1">
                  <div className={`h-[2px] rounded-full transition-all duration-500 ${i <= step ? 'bg-[#1A1612]' : 'bg-[#E8E0D5]'}`} />
                  <p className={`text-[10px] tracking-[0.12em] uppercase font-glacial mt-1.5 transition-colors ${i === step ? 'text-[#1A1612]' : 'text-[#C0B8B0]'}`}>{s}</p>
                </div>
              ))}
            </div>
          )}

          <div className="px-8 py-6 flex-1 overflow-y-auto min-h-0">
            <AnimatePresence mode="wait">

              {step === 0 && (
                <motion.div key="s0" {...fade}>
                  <p className="text-[16px] text-[#6B5E52] mb-5 font-glacial font-light tracking-wide">
                    How do you prefer to experience your fragrance?
                  </p>
                  <div className="grid grid-cols-1 gap-2.5">
                    {FORMAT_OPTIONS.map(o => (
                      <motion.button key={o.value} whileHover={{ x: 4 }}
                        onClick={() => selectFormat(o.value)}
                        onMouseEnter={() => setHoverImage(o.image)}
                        onMouseLeave={() => setHoverImage(DEFAULT_IMAGE)}
                        className="flex items-center justify-between w-full text-left px-6 py-5 border border-[#E0D8CE] hover:border-[#1A1612] hover:bg-white transition-all group">
                        <div>
                          <p className="text-xl text-[#1A1612] tracking-wide font-qlassy">{o.label}</p>
                          <p className="text-[13px] text-[#9B8E83] font-glacial font-light mt-0.5">{o.sub}</p>
                        </div>
                        <span className="text-[#9B8E83] group-hover:text-[#1A1612] transition-colors text-xl">→</span>
                      </motion.button>
                    ))}
                  </div>
                </motion.div>
              )}

              {step === 1 && (
                <motion.div key="s1" {...fade}>
                  <p className="text-[16px] text-[#6B5E52] mb-5 font-glacial font-light tracking-wide">
                    Who is this fragrance for?
                  </p>
                  <div className="grid grid-cols-1 gap-2.5">
                    {GENDER_OPTIONS.map(o => (
                      <motion.button key={o.value} whileHover={{ x: 4 }}
                        onClick={() => selectGender(o.value)}
                        onMouseEnter={() => setHoverImage(o.image)}
                        onMouseLeave={() => setHoverImage(DEFAULT_IMAGE)}
                        className="flex items-center justify-between w-full text-left px-6 py-5 border border-[#E0D8CE] hover:border-[#1A1612] hover:bg-white transition-all group">
                        <div>
                          <p className="text-xl text-[#1A1612] tracking-wide font-qlassy">{o.label}</p>
                          <p className="text-[13px] text-[#9B8E83] font-glacial font-light mt-0.5">{o.sub}</p>
                        </div>
                        <span className="text-[#9B8E83] group-hover:text-[#1A1612] transition-colors text-xl">→</span>
                      </motion.button>
                    ))}
                  </div>
                  <button onClick={() => { setStep(0); setHoverImage(DEFAULT_IMAGE); }} className="mt-5 text-[11px] tracking-[0.18em] uppercase font-glacial text-[#B8AFA6] hover:text-[#1A1612] transition-colors">← Back</button>
                </motion.div>
              )}

              {step === 2 && (
                <motion.div key="s2" {...fade}>
                  <p className="text-[16px] text-[#6B5E52] mb-4 font-glacial font-light tracking-wide">
                    What kind of fragrance naturally draws you in?
                  </p>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {filteredNotes.map((n, i) => (
                      <motion.button key={n.value} initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: i * 0.05 }}
                        onClick={() => selectNote(n.value)}
                        onMouseEnter={() => setHoverImage(n.image)}
                        onMouseLeave={() => setHoverImage(DEFAULT_IMAGE)}
                        className="text-left p-4 border border-[#E0D8CE] hover:border-[#1A1612] hover:bg-white transition-all group">
                        <p className="text-[16px] text-[#1A1612] tracking-wide leading-tight font-qlassy">{n.label}</p>
                        <p className="text-[12px] text-[#9B8E83] font-glacial mt-1 leading-relaxed font-light">{n.desc}</p>
                      </motion.button>
                    ))}
                  </div>
                  <button onClick={() => { setStep(1); setHoverImage(DEFAULT_IMAGE); }} className="mt-5 text-[11px] tracking-[0.18em] uppercase font-glacial text-[#B8AFA6] hover:text-[#1A1612] transition-colors">← Back</button>
                </motion.div>
              )}

              {step === 3 && (
                <motion.div key="s3" {...fade}>
                  <p className="text-[16px] text-[#6B5E52] mb-4 font-glacial font-light tracking-wide">
                    What impression would you like your fragrance to leave?
                  </p>
                  <div className="grid grid-cols-2 gap-2">
                    {IMPRESSIONS.map((im, i) => (
                      <motion.button key={im.value} initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: i * 0.05 }}
                        onClick={() => selectImpression(im.value)}
                        onMouseEnter={() => setHoverImage(im.image)}
                        onMouseLeave={() => setHoverImage(DEFAULT_IMAGE)}
                        className="text-left p-4 border border-[#E0D8CE] hover:border-[#1A1612] hover:bg-white transition-all group">
                        <p className="text-[16px] text-[#1A1612] tracking-wide font-qlassy">{im.label}</p>
                      </motion.button>
                    ))}
                  </div>
                  <button onClick={() => { setStep(2); setHoverImage(DEFAULT_IMAGE); }} className="mt-5 text-[11px] tracking-[0.18em] uppercase font-glacial text-[#B8AFA6] hover:text-[#1A1612] transition-colors">← Back</button>
                </motion.div>
              )}

              {step === 4 && result && (
                <motion.div key="s4" {...fade} className="pt-1">
                  <p className="text-[11px] tracking-[0.2em] text-[#1A1612] uppercase font-glacial mb-4">Your Signature Fragrance</p>

                  <div className="mb-5 pb-5 border-b border-[#E8E0D5]">
                    <div className="flex items-baseline gap-3 mb-1">
                      <h3 className="text-4xl text-[#1A1612] tracking-wider font-qlassy">{result.primary}</h3>
                      <span className="text-[10px] text-white bg-[#1A1612] px-2 py-0.5 font-glacial tracking-widest uppercase">Best Match</span>
                    </div>
                    {primaryInfo && (
                      <>
                        <p className="text-[12px] text-[#1A1612] font-glacial tracking-widest uppercase mb-1">{primaryInfo.noteSegment}</p>
                        <p className="text-[12px] text-[#9B8E83] font-glacial tracking-wide mb-2">{primaryInfo.identity}</p>
                        <p className="text-[14px] text-[#6B5E52] font-glacial font-light leading-relaxed italic">{primaryInfo.story}</p>
                      </>
                    )}
                  </div>

                  <div className="mb-6">
                    <p className="text-[10px] tracking-[0.2em] text-[#B8AFA6] uppercase font-glacial mb-3">You May Also Enjoy</p>
                    <div
                      className="flex gap-4 items-center cursor-default"
                      onMouseEnter={() => secondaryInfo?.image && setHoverImage(secondaryInfo.image)}
                      onMouseLeave={() => primaryInfo?.image && setHoverImage(primaryInfo.image)}
                    >
                      <div className="relative w-10 h-14 flex-shrink-0 overflow-hidden bg-[#EEE8DF]">
                        {secondaryInfo?.image && <Image src={secondaryInfo.image} alt={result.secondary} fill className="object-contain" />}
                      </div>
                      <div>
                        <h4 className="text-xl text-[#1A1612] tracking-wider font-qlassy">{result.secondary}</h4>
                        {secondaryInfo && (
                          <p className="text-[11px] text-[#9B8E83] font-glacial tracking-widest uppercase mt-0.5">{secondaryInfo.noteSegment}</p>
                        )}
                      </div>
                    </div>
                  </div>

                  <div className="flex gap-3">
                    <button
                      onClick={() => { setStep(0); setFormat(''); setGender(''); setNoteSegment(''); setImpression(''); setResult(null); setHoverImage(DEFAULT_IMAGE); }}
                      className="flex-1 py-3.5 border border-[#E0D8CE] text-[12px] tracking-[0.18em] text-[#6B5E52] uppercase font-glacial hover:border-[#1A1612] hover:text-[#1A1612] transition-all"
                    >Restart</button>
                    <button
                      className="flex-1 py-3.5 bg-[#1A1612] text-[12px] tracking-[0.18em] text-white uppercase font-glacial hover:bg-black transition-all"
                      onClick={() => { handleClose(); router.push(primaryInfo?.url || '/'); }}
                    >Explore {result.primary}</button>
                  </div>
                </motion.div>
              )}

            </AnimatePresence>
          </div>

          <div className="h-[1px] w-full bg-gradient-to-r from-transparent via-[#1A1612] to-transparent opacity-20 flex-shrink-0" />
        </div>
      </motion.div>
    </div>
  );
};

export default PerfumeFinder;