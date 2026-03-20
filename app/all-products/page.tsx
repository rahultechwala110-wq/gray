"use client";

import { useState, useEffect } from "react";
import NavigationLight from '../components/layout/NavigationLight';
import AllProduct from '../components/sections/AllProducts';
import Footer from '../components/layout/Footer';


export default function Products() {
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
        <AllProduct/>
      </main>
      <Footer />

    </>
  );
}