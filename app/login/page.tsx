"use client";

import { useState, useEffect } from "react";
import LoginPage from '../components/sections/LoginPage';


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
      <main>
        <LoginPage/>
      </main>

    </>
  );
}