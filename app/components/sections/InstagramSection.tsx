"use client";

import { useState, useEffect } from "react";
import Image from "next/image";
import { motion, AnimatePresence } from "framer-motion";
import { X, ChevronLeft, ChevronRight } from "lucide-react";

type InstaImage = { id: number; image: string };

type Settings = {
  label:        string;
  title:        string;
  hashtag:      string;
  scroll_speed: number;
  is_enabled:   number;
};

const FALLBACK_IMAGES: InstaImage[] = [
  { id: 1, image: "/instagram/image-1.jpg" },
  { id: 2, image: "/instagram/image-2.jpg" },
  { id: 3, image: "/instagram/image-three.jpg" },
  { id: 4, image: "/instagram/image-4.jpg" },
  { id: 5, image: "/instagram/image-5.jpg" },
];

const FALLBACK_SETTINGS: Settings = {
  label:        "Follow us",
  title:        "let's connected",
  hashtag:      "#thegrayuniverse",
  scroll_speed: 30,
  is_enabled:   1,
};

const getLayoutClasses = (index: number) => {
  const patternIndex = index % 5;
  if (patternIndex === 0) return "w-[340px] md:w-[380px] lg:w-[420px] h-[500px] md:h-[550px] lg:h-[600px]";
  if (patternIndex === 1) return "w-[310px] md:w-[350px] lg:w-[380px] h-[460px] md:h-[510px] lg:h-[550px]";
  if (patternIndex === 2) return "w-[260px] md:w-[300px] lg:w-[330px] h-[390px] md:h-[440px] lg:h-[480px]";
  if (patternIndex === 3) return "w-[300px] md:w-[340px] lg:w-[370px] h-[440px] md:h-[490px] lg:h-[530px]";
  return "w-[290px] md:w-[330px] lg:w-[360px] h-[420px] md:h-[470px] lg:h-[510px]";
};

export default function InstagramSection() {
  const [images, setImages]     = useState<InstaImage[]>([]);
  const [settings, setSettings] = useState<Settings>(FALLBACK_SETTINGS);
  const [loading, setLoading]   = useState(true);
  const [selectedIndex, setSelectedIndex] = useState<number | null>(null);

  useEffect(() => {
    fetch("/api/instagram")
      .then((r) => r.json())
      .then((res) => {
        if (res.success && res.data.length > 0) {
          setImages(res.data);
        } else {
          setImages(FALLBACK_IMAGES);
        }
        if (res.settings) setSettings(res.settings);
      })
      .catch(() => {
        setImages(FALLBACK_IMAGES);
        setSettings(FALLBACK_SETTINGS);
      })
      .finally(() => setLoading(false));
  }, []);

  const closeModal = () => setSelectedIndex(null);
  const nextImage  = () => { if (selectedIndex !== null) setSelectedIndex((selectedIndex + 1) % images.length); };
  const prevImage  = () => { if (selectedIndex !== null) setSelectedIndex((selectedIndex - 1 + images.length) % images.length); };

  useEffect(() => {
    const handleKey = (e: KeyboardEvent) => {
      if (e.key === "Escape")     closeModal();
      if (e.key === "ArrowRight") nextImage();
      if (e.key === "ArrowLeft")  prevImage();
    };
    window.addEventListener("keydown", handleKey);
    return () => window.removeEventListener("keydown", handleKey);
  });

  if (loading || settings.is_enabled === 0) return null;

  const displayImages = [...images, ...images];

  return (
    <section className="relative pt-0 pb-6 md:py-16 bg-[#F9F6F1] overflow-hidden">

      {/* Floral Watermark — next/image রাখো কারণ এটা local file */}
      <div className="absolute bottom-0 right-0 pointer-events-none opacity-20 z-0 w-[300px] md:w-[450px]">
        <Image src="/Floral.png" alt="decoration" width={450} height={350} className="object-contain" />
      </div>

      <div className="relative z-10 max-w-full mx-auto">

        {/* Header */}
        <div className="text-center mb-14 px-6">
          <p className="text-[13px] tracking-[0.4em] uppercase font-semibold text-gray-600 block mb-4">
            {settings.label}
          </p>
          <h2 className="text-4xl md:text-5xl font-qlassy text-[#1A1A1A] mb-3 tracking-wide leading-none">
            {settings.title}
          </h2>
          <p className="text-xl md:text-2xl font-glacial text-gray-700 tracking-wider">
            {settings.hashtag}
          </p>
        </div>

        {/* Marquee */}
        <div className="relative w-full flex overflow-hidden py-10 mt-10">
          <div
            className="flex w-max items-center gap-6 md:gap-8 pr-8"
            style={{ animation: `scrollLeft ${settings.scroll_speed}s linear infinite` }}
          >
            {displayImages.map((img, index) => (
              <div
                key={index}
                onClick={() => setSelectedIndex(index % images.length)}
                className={`relative flex-shrink-0 overflow-hidden bg-gray-200 cursor-pointer ${getLayoutClasses(index)}`}
              >
                <img
                  src={img.image}
                  alt="Gallery"
                  className="absolute inset-0 w-full h-full object-cover"
                />
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Fullscreen Lightbox */}
      <AnimatePresence>
        {selectedIndex !== null && (
          <motion.div
            className="fixed inset-0 bg-black/95 backdrop-blur-md flex items-center justify-center z-50"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
          >
            <button onClick={closeModal} className="absolute top-6 right-6 text-white z-50">
              <X size={32} />
            </button>
            <button onClick={prevImage} className="absolute left-6 text-white z-50">
              <ChevronLeft size={40} />
            </button>
            <button onClick={nextImage} className="absolute right-6 text-white z-50">
              <ChevronRight size={40} />
            </button>
            <motion.div
              key={selectedIndex}
              initial={{ scale: 0.85, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.85, opacity: 0 }}
              transition={{ duration: 0.4 }}
              className="relative w-[90%] md:w-[70%] lg:w-[50%] h-[80vh]"
            >
              <img
                src={images[selectedIndex].image}
                alt="Full View"
                className="w-full h-full object-contain"
              />
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>

      <style jsx>{`
        @keyframes scrollLeft {
          0%   { transform: translateX(0%); }
          100% { transform: translateX(-50%); }
        }
      `}</style>
    </section>
  );
}