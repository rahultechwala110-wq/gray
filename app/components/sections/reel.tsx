"use client";

import { motion } from "framer-motion";
import { useState, useEffect, useRef } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";

type Reel = { id: number; video: string };

type Settings = {
  marquee_text:    string;
  marquee_color:   string;
  marquee_opacity: number;
  marquee_enabled: number;
};

const FALLBACK_REELS: Reel[] = [
  { id: 1, video: "/reel/reel-1.mp4" },
  { id: 2, video: "/reel/reel-2.mp4" },
  { id: 3, video: "/reel/reel-3.mp4" },
  { id: 4, video: "/reel/reel-4.mp4" },
  { id: 5, video: "/reel/reel-5.mp4" },
];

const FALLBACK_SETTINGS: Settings = {
  marquee_text:    "GRAY",
  marquee_color:   "#000000",
  marquee_opacity: 20,
  marquee_enabled: 1,
};

export default function ReelSection() {
  const [reels, setReels]       = useState<Reel[]>([]);
  const [settings, setSettings] = useState<Settings>(FALLBACK_SETTINGS);
  const [index, setIndex]       = useState(0);
  const [isPaused, setIsPaused] = useState(false);
  const [loading, setLoading]   = useState(true);
  const videoRefs = useRef<(HTMLVideoElement | null)[]>([]);
  const sectionRef = useRef<HTMLElement | null>(null);

 useEffect(() => {
  fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/reels.php`)
    .then((r) => r.json())
    .then((res) => {
      if (res.success && res.data.length > 0) {
        setReels(res.data.map((r: Reel) => ({
          ...r,
          video: r.video || '',
        })));
      } else {
        setReels(FALLBACK_REELS);
      }
      if (res.settings) setSettings(res.settings);
    })
    .catch(() => {
      setReels(FALLBACK_REELS);
      setSettings(FALLBACK_SETTINGS);
    })
    .finally(() => {
      setLoading(false);
      setIndex(0);
    });
}, []);

  useEffect(() => {
    if (isPaused || reels.length === 0) return;
    const timer = setInterval(() => {
      setIndex((prev) => (prev + 1) % reels.length);
    }, 10000);
    return () => clearInterval(timer);
  }, [index, isPaused, reels.length]);

  useEffect(() => {
    videoRefs.current.forEach((video, i) => {
      if (!video) return;
      if (i === index) {
        video.muted = true;
        video.play().catch(() => {});
      } else {
        video.pause();
        video.currentTime = 0;
      }
    });
  }, [index, reels]);

  const prevSlide = () => setIndex((prev) => (prev - 1 + reels.length) % reels.length);
  const nextSlide = () => setIndex((prev) => (prev + 1) % reels.length);

  if (loading) return (
    <section className="relative py-32 bg-[#F9F6F1] min-h-[900px] flex items-center justify-center">
      <div className="text-gray-400 text-lg">Loading reels...</div>
    </section>
  );

  const marqueeText = Array(12).fill(`${settings.marquee_text} •`).join(" ");

  return (
    <section
      ref={sectionRef}
      className="relative pb-12 py-32 bg-[#F9F6F1] overflow-hidden min-h-[900px]"
    >
      {/* Transparent Scrolling Text Strip */}
      {settings.marquee_enabled === 1 && (
        <div className="absolute top-20 left-0 w-full overflow-hidden z-0 rotate-[6deg] pointer-events-none border-y border-gray-400/10">
          <div className="flex whitespace-nowrap animate-marquee py-4">
            {[0, 1].map((k) => (
              <span
                key={k}
                style={{
                  color:   settings.marquee_color,
                  opacity: settings.marquee_opacity / 100,
                }}
                className="font-glacial tracking-[0.5em] text-4xl uppercase"
              >
                {marqueeText}&nbsp;
              </span>
            ))}
          </div>
        </div>
      )}

      {/* Main Container */}
      <div className="relative max-w-[1400px] w-full mx-auto flex items-end justify-center h-[750px] px-4 md:px-8 z-10 pb-10">

        {/* Left Arrow */}
        <button
          onClick={prevSlide}
          className="absolute left-2 md:left-12 lg:left-24 z-30 bg-[#D5C6B3] text-white p-3 rounded-full shadow-lg hover:scale-105 transition-transform top-1/2 -translate-y-1/2"
        >
          <ChevronLeft size={24} />
        </button>

        {/* Reels Wrapper */}
        <div className="relative w-full max-w-5xl h-[600px] flex items-center justify-center">
          {reels.map((reel, i) => {
            const diff = (i - index + reels.length) % reels.length;

            let xPos       = "0%";
            let scaleVal   = 1;
            let opacityVal = 1;
            let zIndexVal  = 10;

            if (diff === 0) {
              xPos = "0%";
            } else if (diff === 1) {
              xPos       = "105%";
              scaleVal   = 0.85;
              opacityVal = 0.6;
              zIndexVal  = 5;
            } else if (diff === reels.length - 1) {
              xPos       = "-105%";
              scaleVal   = 0.85;
              opacityVal = 0.6;
              zIndexVal  = 5;
            } else {
              xPos       = diff > reels.length / 2 ? "-200%" : "200%";
              scaleVal   = 0.5;
              opacityVal = 0;
              zIndexVal  = 0;
            }

            return (
              <motion.div
                key={reel.id}
                initial={false}
                animate={{
                  x: xPos,
                  scale: scaleVal,
                  opacity: opacityVal,
                  zIndex: zIndexVal,
                }}
                transition={{
                  duration: 0.6,
                  ease: [0.25, 0.46, 0.45, 0.94],
                  type: "tween",
                }}
                onMouseEnter={() => setIsPaused(true)}
                onMouseLeave={() => setIsPaused(false)}
                className="absolute w-[340px] h-[600px] rounded-[30px] overflow-hidden shadow-2xl bg-black cursor-pointer"
              >
                <video
                  ref={(el) => { videoRefs.current[i] = el; }}
                  src={reel.video}
                  loop
                  muted
                  autoPlay={i === index}
                  playsInline
                  controls={false}
                  onMouseEnter={(e) => {
                    if (i === index) e.currentTarget.controls = true;
                  }}
                  onMouseLeave={(e) => {
                    if (i === index) e.currentTarget.controls = false;
                  }}
                  className="w-full h-full object-cover"
                />
              </motion.div>
            );
          })}
        </div>

        {/* Right Arrow */}
        <button
          onClick={nextSlide}
          className="absolute right-2 md:right-12 lg:right-24 z-30 bg-[#D5C6B3] text-white p-3 rounded-full shadow-lg hover:scale-105 transition-transform top-1/2 -translate-y-1/2"
        >
          <ChevronRight size={24} />
        </button>
      </div>
    </section>
  );
}