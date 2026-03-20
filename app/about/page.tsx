"use client";

import { useState, useEffect } from "react";
import NavigationLight from '../components/layout/NavigationLight';
import About from '../components/sections/About';
import Footer from '../components/layout/Footer';


export default function about() {
  const [isFinderOpen, setIsFinderOpen] = useState(false);

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsFinderOpen(true);
    }, 5000);
    return () => clearTimeout(timer);
  }, []);

  return (
    <>
      <NavigationLight />
      <main>
        <About/>
      </main>
      <Footer />

    </>
  );
}