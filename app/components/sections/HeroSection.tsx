'use client';
import { useState, useRef, useEffect } from 'react';
import Icon from '../ui/Icon';

interface HeroData {
  heading: string;
  subheading: string;
  button_text: string;
  button_link: string;
  video_file: string;
  overlay_opacity: number;
  default_muted: number;
  show_sound_btn: number;
  top_gradient: number;
  bottom_gradient: number;
}

export default function HeroSection() {
  const [hero, setHero] = useState<HeroData | null>(null);
  const [isMuted, setIsMuted] = useState(true);
  const videoRef = useRef<HTMLVideoElement>(null);

  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/hero.php`)
      .then(r => r.json())
      .then(data => {
        setHero(data);
        setIsMuted(data.default_muted === 1);
        if (videoRef.current) {
          videoRef.current.muted = data.default_muted === 1;
        }
      });
  }, []);

  const toggleMute = () => {
    if (videoRef.current) {
      videoRef.current.muted = !videoRef.current.muted;
      setIsMuted(videoRef.current.muted);
    }
  };

  const opacity = hero ? Number(hero.overlay_opacity) : 0.5;

  // Admin se upload hoti hai: D:/XAMPP/htdocs/gray/public/hero-section/banner.mp4
  // Next.js ise load karta hai: /hero-section/banner.mp4
  const videoSrc = hero?.video_file
    ? `/hero-section/${hero.video_file}`
    : '/hero-section/banner.mp4';

  return (
    <header className="relative h-screen w-full overflow-hidden bg-black">

      <div className="absolute inset-0">
        <video
          ref={videoRef}
          autoPlay
          loop
          muted={isMuted}
          playsInline
          className="w-full h-full object-cover"
        >
          <source src={videoSrc} type="video/mp4" />
        </video>
        <div className="absolute inset-0 bg-black pointer-events-none"
             style={{ opacity }} />
      </div>

      {hero?.top_gradient === 1 && (
        <div className="absolute top-0 left-0 w-full h-[40%]
          bg-gradient-to-b from-black via-black/60 to-transparent z-10
          pointer-events-none" />
      )}

      {hero?.bottom_gradient === 1 && (
        <div className="absolute bottom-0 left-0 w-full h-[40%]
          bg-gradient-to-t from-black via-black/60 to-transparent z-10
          pointer-events-none" />
      )}

      {hero?.show_sound_btn === 1 && (
        <div className="absolute bottom-10 right-10 z-30">
          <button onClick={toggleMute}
            className="w-12 h-12 border border-white/20 rounded-full
              flex items-center justify-center hover:bg-white/10
              transition-all backdrop-blur-md group">
            <Icon name={isMuted ? "volume_off" : "volume_up"}
              className="text-white text-xl" />
          </button>
        </div>
      )}

    </header>
  );
}