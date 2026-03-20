import type { Metadata } from 'next';
import Brave from '@/app/components/sections/brave';
import NavigationLight from '@/app/components/layout/NavigationLight';
import Footer from '@/app/components/layout/Footer';

export const metadata: Metadata = {
  title: "BRAVE Men's Perfume 55ml | Long‑Lasting Woody Amber EDP by GRAY",
  description:
    "Experience BRAVE by GRAY, a long‑lasting men's perfume with refined woody amber notes. Designed for evenings defined by confidence and control.",
  alternates: {
    canonical: '/brave-mens-perfume-55ml',
  },
};

export default function Page() {
  return (
    <>
      <NavigationLight />
      <main>
        <Brave />
      </main>
      <Footer />
    </>
  );
}