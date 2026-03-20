"use client";

import Image from "next/image";
import { MoveLeft, MoveRight, Leaf, Droplets, Sparkles, Star, Heart, Zap, Shield, Award, Gift, Wind, Sun, Moon, ArrowUpRight } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { useState, useEffect } from "react";
import Link from 'next/link';

interface Feature {
  icon: string;
  title: string;
  description: string;
}

interface FeaturedProductData {
  id: number;
  name: string;
  image: string;
  floral: string;
  href: string;
  features: Feature[];
}

const iconMap: Record<string, React.ReactNode> = {
  Leaf:     <Leaf size={24} />,
  Droplets: <Droplets size={24} />,
  Sparkles: <Sparkles size={24} />,
  Star:     <Star size={24} />,
  Heart:    <Heart size={24} />,
  Zap:      <Zap size={24} />,
  Shield:   <Shield size={24} />,
  Award:    <Award size={24} />,
  Gift:     <Gift size={24} />,
  Wind:     <Wind size={24} />,
  Sun:      <Sun size={24} />,
  Moon:     <Moon size={24} />,
};

const fallbackProducts: FeaturedProductData[] = [
  // ── Man ──────────────────────────────────────────────────────────────────
  {
    id: 1, name: "Brave", image: "/products/featured-product-1.png", floral: "/Flower.png",
    href: "/brave-mens-perfume-55ml",
    features: [
      { icon: "Leaf",     title: "Strong Sillage",      description: "A powerful presence that lingers elegantly." },
      { icon: "Droplets", title: "Long Lasting",         description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Sparkles", title: "25%+ Perfume Oil",     description: "Rich woody amber with honeyed warmth." },
    ],
  },
  {
    id: 2, name: "Boss", image: "/products/featured-product-2.png", floral: "/Flower.png",
    href: "/boss-mens-perfume-55m",
    features: [
      { icon: "Zap",      title: "Bold Projection",     description: "Strong presence for celebration occasions." },
      { icon: "Droplets", title: "Long Lasting",         description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Sparkles", title: "Spicy Woody Amber",   description: "Citrus brightness over bold spicy woods." },
    ],
  },
  {
    id: 3, name: "Gentle", image: "/products/featured-product-3.png", floral: "/Flower.png",
    href: "/gentle-mens-perfume-55ml",
    features: [
      { icon: "Leaf",     title: "Daily Signature",     description: "Designed for office, travel and everyday wear." },
      { icon: "Droplets", title: "Long Lasting",         description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Wind",     title: "Fresh Citrus Woody",  description: "Clean citrus with lavender and cacao warmth." },
    ],
  },
  {
    id: 4, name: "Bold", image: "/products/featured-product-2.png", floral: "/Flower.png",
    href: "/gold-men-perfume-55ml",
    features: [
      { icon: "Moon",     title: "After Dark",          description: "Perfect for festive evenings and formal occasions." },
      { icon: "Droplets", title: "Long Lasting",         description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Sparkles", title: "Marine Spicy",        description: "Cinnamon and marine notes over amber woods." },
    ],
  },
  {
    id: 5, name: "Generous", image: "/products/featured-product-1.png", floral: "/Flower.png",
    href: "/generous-mens-perfume-55ml",
    features: [
      { icon: "Heart",    title: "Intimate Warmth",     description: "Close, inviting projection for after-dark moments." },
      { icon: "Droplets", title: "Long Lasting",         description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Leaf",     title: "Vanilla Rum Amber",   description: "Smooth woody core with rum-like sweetness." },
    ],
  },
  {
    id: 6, name: "Groomed", image: "/products/featured-product-3.png", floral: "/Flower.png",
    href: "/groomed-mens-perfume-55m",
    features: [
      { icon: "Award",    title: "Polished Elegance",   description: "A finishing touch for weddings and formal events." },
      { icon: "Droplets", title: "Long Lasting",         description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Sparkles", title: "Sandalwood Amber",    description: "Classic sandalwood enriched with warm amber spice." },
    ],
  },
  // ── Woman ─────────────────────────────────────────────────────────────────
  {
    id: 7, name: "Bliss", image: "/products/featured-product-1.png", floral: "/Flower.png",
    href: "/bliss-womens-perfume-55ml",
    features: [
      { icon: "Heart",    title: "Serene & Soft",       description: "Calm, radiant presence for elegant daytime moments." },
      { icon: "Droplets", title: "Long Lasting",         description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Wind",     title: "Aquatic Floral",      description: "Marine freshness with delicate floral musk." },
    ],
  },
  {
    id: 8, name: "Gorgeous", image: "/products/featured-product-2.png", floral: "/Flower.png",
    href: "/gorgeous-womens-perfume-55ml",
    features: [
      { icon: "Star",     title: "Radiant Presence",    description: "Noticeable projection for confident after-dark moments." },
      { icon: "Droplets", title: "Long Lasting",         description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Sparkles", title: "Floral Citrus Amber", description: "Delicate florals with warm amber and woody depth." },
    ],
  },
  {
    id: 9, name: "Braveheart", image: "/products/featured-product-3.png", floral: "/Flower.png",
    href: "/braveheart-womens-perfume-55ml",
    features: [
      { icon: "Zap",      title: "Courageous Spirit",    description: "Expressive and confident for evening outings." },
      { icon: "Droplets", title: "Long Lasting",          description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Wind",     title: "Aquatic Woody Fruity", description: "Marine freshness with fruity warmth and amber depth." },
    ],
  },
  {
    id: 10, name: "Glorious", image: "/products/featured-product-1.png", floral: "/Flower.png",
    href: "/glorious-womens-perfume-55ml",
    features: [
      { icon: "Shield",   title: "Commanding Grace",    description: "Strong projection for weddings and festive evenings." },
      { icon: "Droplets", title: "Long Lasting",         description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Moon",     title: "Oud Leather Amber",   description: "Rich oud and leather anchored by warm amber musk." },
    ],
  },
  {
    id: 11, name: "Gifted", image: "/products/featured-product-2.png", floral: "/Flower.png",
    href: "/gifted-womens-perfume-55ml",
    features: [
      { icon: "Leaf",     title: "Daily Signature",     description: "Intimate and composed projection for everyday wear." },
      { icon: "Droplets", title: "Long Lasting",         description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Gift",     title: "Lavender Oud Herbal", description: "Soothing lavender with deep oud and earthy herbs." },
    ],
  },
  {
    id: 12, name: "Brilliance", image: "/products/featured-product-3.png", floral: "/Flower.png",
    href: "/brilliance-womens-perfume-55ml",
    features: [
      { icon: "Sun",      title: "Inner Glow",          description: "Graceful projection for office and everyday elegance." },
      { icon: "Droplets", title: "Long Lasting",         description: "Exceptional longevity — 24 hours or more on fabric." },
      { icon: "Sparkles", title: "Vanilla Rose Woody",  description: "Soft rose and vanilla illuminated by citrus brightness." },
    ],
  },
  // ── Aroma Pod ─────────────────────────────────────────────────────────────
  {
    id: 13, name: "B612", image: "/products/product-13.png", floral: "/Flower.png",
    href: "/aroma-pod/b612",
    features: [
      { icon: "Wind",     title: "Home Fragrance",      description: "Fills your space with a continuous, lasting aroma." },
      { icon: "Leaf",     title: "Natural Extracts",    description: "Crafted with pure botanical and natural scent extracts." },
      { icon: "Sparkles", title: "Long Diffusion",      description: "Hours of steady, gentle fragrance diffusion." },
    ],
  },
  {
    id: 14, name: "Bulge", image: "/products/product-14.png", floral: "/Flower.png",
    href: "/aroma-pod/bulge",
    features: [
      { icon: "Sun",      title: "Bright & Fresh",      description: "Uplifting citrus notes to energize your space." },
      { icon: "Leaf",     title: "Natural Extracts",    description: "Crafted with pure botanical and natural scent extracts." },
      { icon: "Sparkles", title: "Long Diffusion",      description: "Hours of steady, gentle fragrance diffusion." },
    ],
  },
  {
    id: 15, name: "Brahe", image: "/products/product-15.png", floral: "/Flower.png",
    href: "/aroma-pod/brahe",
    features: [
      { icon: "Moon",     title: "Calm & Soothing",     description: "Warm, relaxing tones for unwinding at the end of the day." },
      { icon: "Leaf",     title: "Natural Extracts",    description: "Crafted with pure botanical and natural scent extracts." },
      { icon: "Sparkles", title: "Long Diffusion",      description: "Hours of steady, gentle fragrance diffusion." },
    ],
  },
  {
    id: 16, name: "Glese", image: "/products/product-16.png", floral: "/Flower.png",
    href: "/aroma-pod/glese",
    features: [
      { icon: "Heart",    title: "Warm & Inviting",     description: "Cozy amber warmth that makes any room feel welcoming." },
      { icon: "Leaf",     title: "Natural Extracts",    description: "Crafted with pure botanical and natural scent extracts." },
      { icon: "Sparkles", title: "Long Diffusion",      description: "Hours of steady, gentle fragrance diffusion." },
    ],
  },
  {
    id: 17, name: "Ganymede", image: "/products/product-17.png", floral: "/Flower.png",
    href: "/aroma-pod/ganymede",
    features: [
      { icon: "Star",     title: "Deep & Mysterious",   description: "Rich woody and musky layers for an evocative atmosphere." },
      { icon: "Leaf",     title: "Natural Extracts",    description: "Crafted with pure botanical and natural scent extracts." },
      { icon: "Sparkles", title: "Long Diffusion",      description: "Hours of steady, gentle fragrance diffusion." },
    ],
  },
  {
    id: 18, name: "Gaspra", image: "/products/product-18.png", floral: "/Flower.png",
    href: "/aroma-pod/gaspra",
    features: [
      { icon: "Shield",   title: "Pure & Clean",        description: "Crisp, airy notes for a refreshing and purified space." },
      { icon: "Leaf",     title: "Natural Extracts",    description: "Crafted with pure botanical and natural scent extracts." },
      { icon: "Sparkles", title: "Long Diffusion",      description: "Hours of steady, gentle fragrance diffusion." },
    ],
  },
];

export default function FeaturedProduct() {
  const [products, setProducts] = useState<FeaturedProductData[]>(fallbackProducts);
  const [index, setIndex]       = useState(0);

  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/featured-product.php`, { cache: 'no-store' })
      .then(r => r.json())
      .then((data: FeaturedProductData[]) => {
        if (data?.length) {
          setProducts(data.map(p => ({
            ...p,
            image:  p.image  || '',
            floral: p.floral || '/Flower.png',
          })));
        }
      })
      .catch(() => {});
  }, []);

  const nextSlide = () => setIndex(prev => (prev + 1) % products.length);
  const prevSlide = () => setIndex(prev => prev === 0 ? products.length - 1 : prev - 1);
  const product   = products[index];

  return (
    <section className="relative bg-[#F9F6F1] min-h-screen flex items-center justify-center overflow-hidden pt-0 pb-12 md:py-16">
      <div className="relative z-10 max-w-7xl mx-auto w-full grid grid-cols-1 md:grid-cols-[80px_1.2fr_60px_1fr] lg:grid-cols-[100px_1.2fr_60px_1fr] items-center gap-4 md:gap-0">

        {/* 1. Vertical Product Name */}
        <div className="flex justify-center md:justify-center items-center h-full relative">
          <h1 className="text-[50px] sm:text-[70px] md:text-[85px] lg:text-[100px] font-qlassy font-bold text-[#D8CEC2] leading-none
                         rotate-0 md:-rotate-90 tracking-tight whitespace-nowrap select-none opacity-60
                         md:-mr-20 lg:-mr-24 transition-all duration-500">
            {product.name}
          </h1>
        </div>

        {/* 2. Product Image + Floral */}
        <div className="relative flex justify-center items-center h-full min-h-[300px] md:min-h-[500px]">
          <AnimatePresence mode="wait">
            <motion.div key={`floral-${index}`}
              initial={{ opacity: 0, scale: 0.6, rotate: -15, y: 30 }}
              animate={{ opacity: 0.25, scale: 1, rotate: 0, y: 0 }}
              exit={{ opacity: 0, scale: 1.1, rotate: 5, y: -20 }}
              transition={{ duration: 0.8, ease: [0.22, 1, 0.36, 1] }}
              className="absolute w-[280px] sm:w-[400px] md:w-[500px] lg:w-[650px] aspect-square pointer-events-none z-0">
              <Image src={product.floral || '/Flower.png'} alt="floral decoration" fill className="object-contain" />
            </motion.div>
          </AnimatePresence>

          <AnimatePresence mode="wait">
            <motion.div key={product.image}
              initial={{ opacity: 0, scale: 0.8, y: 20 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.8, y: -20 }}
              transition={{ duration: 0.5, ease: "easeOut" }}
              className="relative w-[267px] sm:w-[260px] md:w-[340px] lg:w-[420px] aspect-[3/4] z-10">
              <Image src={product.image} alt={product.name} fill priority
                     className="object-contain drop-shadow-[20px_40px_60px_rgba(0,0,0,0.2)]" />
            </motion.div>
          </AnimatePresence>
        </div>

        {/* 3. Controls */}
        <div className="flex flex-row md:flex-col items-center justify-center gap-6 z-20">
          <button onClick={prevSlide} className="w-12 h-12 rounded-full border border-[#D1C7BC] flex items-center justify-center hover:bg-[#D5C6B3] hover:border-[#D5C6B3] transition-all group">
            <MoveLeft size={20} className="text-[#7E766D] group-hover:text-white" />
          </button>
          <button onClick={nextSlide} className="w-12 h-12 rounded-full border border-[#D1C7BC] flex items-center justify-center hover:bg-[#D5C6B3] hover:border-[#D5C6B3] transition-all group">
            <MoveRight size={20} className="text-[#7E766D] group-hover:text-white" />
          </button>
        </div>

        {/* 4. Features & Button */}
        <div className="flex flex-col justify-center text-center md:text-left md:pl-8 lg:pl-16">
          <AnimatePresence mode="wait">
            <motion.div key={index}
              initial={{ opacity: 0, x: 30 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -30 }}
              transition={{ duration: 0.4 }}
              className="space-y-8 md:space-y-12">
              <div className="space-y-8">
                {product.features.map((item, i) => (
                  <div key={i} className="flex flex-col items-center md:items-start group">
                    <div className="flex items-center gap-4 mb-2 md:mb-3">
                      <span className="text-[#B7AEA3] transition-colors group-hover:text-[#4A443E]">
                        {iconMap[item.icon] || <Leaf size={24} />}
                      </span>
                      <h3 className="text-[16px] md:text-[18px] lg:text-[21px] font-qlassy font-bold text-[#1A1A1A] tracking-tight">
                        {item.title}
                      </h3>
                    </div>
                    <p className="text-[16px] leading-relaxed font-glacial text-gray-600 max-w-[300px] md:max-w-[340px]">
                      {item.description}
                    </p>
                  </div>
                ))}
              </div>

              <div className="pt-6">
                <Link href={product.href} className="inline-flex items-center gap-3 group">
                  <span className="text-[12px] tracking-[0.2em] uppercase font-qlassy font-bold text-gray-900 border-b border-black/20 pb-0.5 group-hover:border-black transition-colors duration-300">
                    Explore More
                  </span>
                  <ArrowUpRight size={14} strokeWidth={2} className="text-gray-400 group-hover:text-gray-900 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all duration-300" />
                </Link>
              </div>
            </motion.div>
          </AnimatePresence>
        </div>

      </div>
    </section>
  );
}