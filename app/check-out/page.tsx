"use client";

import { useState, useEffect } from "react";
import NavigationLight from '../components/layout/NavigationLight';
import Footer from '../components/layout/Footer';
import CheckOutContent from '../components/sections/CheckOutPage'; 

export default function CheckOutPage() {
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
        <CheckOutContent />
      </main>
      <Footer />
    </>
  );
}