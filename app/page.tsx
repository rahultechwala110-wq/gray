"use client";

import { useState, useEffect } from "react";
import { usePathname } from "next/navigation";
import Navigation from './components/layout/Navigation';
import Footer from './components/layout/Footer';
import HeroSection from './components/sections/HeroSection';
import FeaturedFragrances from './components/sections/FeaturedFragrances';
import PhilosophySection from './components/sections/PhilosophySection';
import CollectionsGrid from './components/sections/CollectionsGrid';
import ProductsShowcase from './components/sections/ProductsShowcase';
import FeaturedProduct from './components/sections/FeaturedProduct';
import ParallaxVideo from './components/sections/ParallaxVideo';
import TestimonialsSection from './components/sections/TestimonialsSection';
import Reel from './components/sections/reel';
import InstagramSection from './components/sections/InstagramSection';
import BlogSection from './components/sections/BlogSection';
import PerfumeFinder from './components/sections/PerfumeFinder';

export default function Home() {
  const pathname = usePathname();
  const [isFinderOpen, setIsFinderOpen] = useState(false);

  useEffect(() => {
    if (pathname !== '/') return;
    const alreadySeen = sessionStorage.getItem('pf_shown') === '1';
    if (alreadySeen) return;

    const timer = setTimeout(() => {
      setIsFinderOpen(true);
    }, 5000);

    return () => clearTimeout(timer);
  }, [pathname]);

  return (
    <>
      <style>{`
        :root {
          --loader-color: #F9F6F1;
        }
      `}</style>
      <Navigation />
      <main>
        <HeroSection />
        <FeaturedFragrances />
        <PhilosophySection />
        <CollectionsGrid />
        <ProductsShowcase />
        <FeaturedProduct />
        <ParallaxVideo />
        <TestimonialsSection />
        <Reel />
        <InstagramSection />
        <BlogSection />
      </main>
      <Footer />

      <PerfumeFinder
        isOpen={isFinderOpen}
        onClose={() => {
          setIsFinderOpen(false);
          sessionStorage.setItem('pf_shown', '1');
        }}
      />
    </>
  );
}