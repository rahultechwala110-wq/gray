"use client";

import { useState, useEffect } from "react";
import NavigationLight from '../components/layout/NavigationLight';
import CartPage from '../components/sections/CartPage';
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
        <CartPage/>
      </main>
      <Footer />

    </>
  );
}