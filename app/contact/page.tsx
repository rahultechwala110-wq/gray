"use client";

import { useState, useEffect } from "react";
import NavigationLight from '../components/layout/NavigationLight';
import ContactPage from '../components/sections/ContactPage';
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
        <ContactPage/>
      </main>
      <Footer />

    </>
  );
}