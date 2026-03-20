"use client";

import { useState, useEffect, useRef } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { usePathname } from "next/navigation";

export default function ClientWrapper({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const isHomePage = pathname === "/";
  const [loading, setLoading] = useState(isHomePage); 
  const videoRef = useRef<HTMLVideoElement>(null);

  useEffect(() => {
    if (!isHomePage) {
      setLoading(false);
      return;
    }

    if (videoRef.current) {
      videoRef.current.play().catch(() => console.log("Autoplay blocked"));
    }

    const handleLoad = () => {
      setTimeout(() => setLoading(false), 2000);
    };

    if (document.readyState === "complete") {
      handleLoad();
    } else {
      window.addEventListener("load", handleLoad);
      return () => window.removeEventListener("load", handleLoad);
    }
  }, [isHomePage]);

  return (
    <>
      <AnimatePresence mode="wait">
        {isHomePage && loading && (
          <motion.div
            key="loader"
            initial={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.8, ease: "easeInOut" }}
            className="fixed inset-0 z-[9999] flex items-center justify-center bg-[#F9F6F1]"
          >
            <div className="relative w-full h-full">
              <video
                ref={videoRef}
                src="/preloader.mp4" 
                muted
                playsInline
                autoPlay
                loop
                className="w-full h-full object-cover" 
              />
            </div>
          </motion.div>
        )}
      </AnimatePresence>
      {children}
    </>
  );
}