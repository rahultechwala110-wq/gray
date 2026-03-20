"use client";

import { useRef, useState, useEffect } from "react";
import Icon from '../ui/Icon';

interface ParallaxVideoData {
  video_file: string;
  opacity: number;
  height: number;
  border_radius: number;
  show_sound_btn: number;
  updated_at: string;
}

const defaultData: ParallaxVideoData = {
  video_file: '',
  opacity: 0.80,
  height: 520,
  border_radius: 40,
  show_sound_btn: 1,
  updated_at: '',
};

export default function ParallaxVideo() {
  const videoRef = useRef<HTMLVideoElement>(null);
  const [isMuted, setIsMuted] = useState(true);
  const [data, setData]       = useState<ParallaxVideoData>(defaultData);
  const [videoKey, setVideoKey] = useState('init');

  useEffect(() => {
    fetch('/api/parallax-video', { cache: 'no-store' })
      .then(r => r.json())
      .then((d: ParallaxVideoData) => {
        if (d) {
          setData(d);
          setVideoKey(d.updated_at || Date.now().toString());
        }
      })
      .catch(() => {});
  }, []);

  const toggleMute = () => {
    if (videoRef.current) {
      videoRef.current.muted = !videoRef.current.muted;
      setIsMuted(videoRef.current.muted);
    }
  };

  const videoSrc = data.video_file
  ? `${data.video_file}?v=${videoKey}`
  : `/video.mp4?v=${videoKey}`;

  return (
    <section className="pt-0 pb-12 md:py-16 bg-[#F9F6F1] flex justify-center">
      <div
        className="relative w-[95%] max-w-[1450px] overflow-hidden shadow-xl bg-black"
        style={{
          height:       `${data.height}px`,
          borderRadius: `${data.border_radius}px`,
        }}
      >
        <div className="absolute inset-0">
          <video
            key={videoSrc}
            ref={videoRef}
            autoPlay
            loop
            muted
            playsInline
            className="w-full h-full object-cover block"
            style={{ opacity: Number(data.opacity) }}
          >
            <source src={videoSrc} type="video/mp4" />
          </video>
        </div>

        {data.show_sound_btn === 1 && (
          <div className="absolute bottom-10 right-10 z-30">
            <button
              onClick={toggleMute}
              className="w-12 h-12 border border-white/20 rounded-full flex items-center justify-center hover:bg-white/10 transition-all backdrop-blur-md group"
              title={isMuted ? "Unmute" : "Mute"}
            >
              <Icon
                name={isMuted ? "volume_off" : "volume_up"}
                className="text-white text-xl transition-transform group-active:scale-90"
              />
            </button>
          </div>
        )}
      </div>
    </section>
  );
}