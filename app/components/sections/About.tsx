'use client';

import { useState, useEffect } from 'react';

interface AboutData {
  heading: string;
  subheading: string;
  image1: string;
  quote: string;
  story_heading: string;
  story_content: string;
  image2: string;
}

const fallback: AboutData = {
  heading:       'Our Story',
  subheading:    'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Quis ipsum suspendisse ultrices gravida. Risus commodo viverra maecenas accumsan lacus vel facilisis.',
  image1:        '/about/1.jpg',
  quote:         '"Our objective has always been to formulate skin, hair and body care products of the finest quality; we investigate widely to source plant-based and laboratory-made ingredients, and use only those with a proven record of safety and efficacy."',
  story_heading: 'Our Story',
  story_content: 'Our objective has always been to formulate skin, hair and body care products of the finest quality; we investigate widely to source plant-based and laboratory-made ingredients, and use only those with a proven record of safety and efficacy. In each of our unique stores, informed consultants are pleased to introduce the Aesop range and to guide your selections.',
  image2:        '/about/2.jpg',
};

export default function About() {
  const [data, setData] = useState<AboutData>(fallback);

  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/about.php`, { cache: 'no-store' })
      .then(r => r.json())
      .then((d: AboutData) => {
        if (d?.heading) setData(d);
      })
      .catch(() => {});
  }, []);

  return (
    <section className="bg-[#F9F6F1] text-gray-900 overflow-hidden pt-20 md:pt-40 pb-0 min-h-screen">

      {/* Top Content */}
      <div className="max-w-7xl mx-auto px-6 md:px-12 lg:px-20">
        <div className="max-w-3xl mb-12 md:mb-16">
          <h1 className="text-4xl sm:text-5xl md:text-8xl font-qlassy mb-6 md:mb-8 tracking-tight text-gray-900">
            {data.heading}
          </h1>
          <p className="text-[16px] text-gray-600 font-glacial leading-relaxed max-w-xl">
            {data.subheading}
          </p>
        </div>
      </div>

      {/* Hero Full-width Image */}
      {data.image1 && (
        <div className="relative w-full h-[300px] sm:h-[400px] md:h-[650px] mb-12 md:mb-16 overflow-hidden">
          <img
            src={data.image1}
            alt={data.heading}
            className="absolute inset-0 w-full h-full object-cover"
          />
        </div>
      )}

      {/* Center Quote */}
      {data.quote && (
        <div className="max-w-7xl mx-auto px-6 md:px-12 lg:px-20">
          <div className="max-w-2xl mx-auto text-center mb-20 md:mb-20">
            <p className="text-[16px] text-gray-600 font-glacial leading-relaxed tracking-wide">
              {data.quote}
            </p>
          </div>
        </div>
      )}

      {/* Side-by-Side Section */}
      <div className="flex flex-col md:flex-row items-center w-full mb-20 md:mb-40">

        {/* Text Side */}
        <div className="w-full md:w-[60%] order-2 md:order-1 mt-12 md:mt-0">
          <div className="px-6 md:pl-12 lg:pl-20 md:pr-10">
            <div className="max-w-xl">
              <h2 className="text-4xl sm:text-5xl md:text-8xl font-qlassy mb-6 md:mb-10 text-gray-900 leading-none">
                {data.story_heading}
              </h2>
              <p className="text-[16px] text-gray-600 font-glacial leading-relaxed">
                {data.story_content}
              </p>
            </div>
          </div>
        </div>

        {/* Image Side */}
        {data.image2 && (
          <div className="w-full md:w-[40%] relative h-[350px] sm:h-[450px] md:h-[600px] order-1 md:order-2 self-stretch">
            <img
              src={data.image2}
              alt={data.story_heading}
              className="absolute inset-0 w-full h-full object-cover"
            />
          </div>
        )}

      </div>
    </section>
  );
}