'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { ArrowLeft, ArrowRight, MapPin, Phone, Mail } from 'lucide-react';
import { Facebook, Instagram, Twitter, Linkedin, MessageCircle } from 'lucide-react';

type FormState = {
  name: string;
  email: string;
  subject: string;
  message: string;
};

type Settings = {
  address:          string;
  phone:            string;
  email:            string;
  facebook:         string;
  instagram:        string;
  whatsapp:         string;
  twitter:          string;
  linkedin:         string;
  google_map_embed: string;
  hero_image:       string;
};

const DEFAULTS: Settings = {
  address:          '123 Luxury Lane, Fashion District,\nMumbai, India — 400001',
  phone:            '+91 123 456 7890',
  email:            'info@grayfragrance.com',
  facebook:         '#',
  instagram:        '#',
  whatsapp:         '#',
  twitter:          '#',
  linkedin:         '#',
  google_map_embed: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d241317.1160993892!2d72.7411!3d19.076!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7c63066449c89%3A0x123564e402758163!2sMumbai%2C%20Maharashtra!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin',
  hero_image:       '/contact.jpg',
};

export default function ContactPage() {
  const [form, setForm]           = useState<FormState>({ name: '', email: '', subject: '', message: '' });
  const [focused, setFocused]     = useState<string>('');
  const [submitted, setSubmitted] = useState<boolean>(false);
  const [sending, setSending]     = useState<boolean>(false);
  const [settings, setSettings]   = useState<Settings>(DEFAULTS);

  useEffect(() => {
    fetch('/api/contact')
      .then(r => r.json())
      .then(({ settings: s }) => {
        if (s) {
          setSettings({
            address:          s.address          || DEFAULTS.address,
            phone:            s.phone            || DEFAULTS.phone,
            email:            s.email            || DEFAULTS.email,
            facebook:         s.facebook         || '#',
            instagram:        s.instagram        || '#',
            whatsapp:         s.whatsapp         || '#',
            twitter:          s.twitter          || '#',
            linkedin:         s.linkedin         || '#',
            google_map_embed: s.google_map_embed || DEFAULTS.google_map_embed,
            hero_image:       s.hero_image       || DEFAULTS.hero_image,
          });
        }
      })
      .catch(() => {});
  }, []);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    setForm((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async () => {
    if (!form.name || !form.email || !form.message) return;
    setSending(true);
    try {
      await fetch('/api/contact', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(form),
      });
      setSubmitted(true);
    } catch {
      setSubmitted(true);
    } finally {
      setSending(false);
    }
  };

  const socials = [
    { Icon: Facebook,      href: settings.facebook  },
    { Icon: Instagram,     href: settings.instagram },
    { Icon: MessageCircle, href: settings.whatsapp  },
    { Icon: Twitter,       href: settings.twitter   },
    { Icon: Linkedin,      href: settings.linkedin  },
  ];

  const inputBase: React.CSSProperties = {
    width: '100%', background: 'transparent', border: 'none',
    borderBottom: '1px solid rgba(26,26,26,0.18)', padding: '10px 0',
    fontSize: '14px', color: '#1a1a1a', outline: 'none',
    letterSpacing: '0.03em', transition: 'border-color 0.3s', fontFamily: 'inherit',
  };
  const inputFocused: React.CSSProperties = { borderBottomColor: '#1a1a1a' };

  return (
    <div className="min-h-screen font-glacial" style={{ background: '#F9F6F1', color: '#1a1a1a' }}>

      {/* ── HERO ── */}
      <div className="relative w-full" style={{ height: '52vh', minHeight: '340px' }}>
        // Hero image এ:
<img
  src={settings.hero_image || '/contact.jpg'}
  alt="Contact"
  className="absolute inset-0 w-full h-full object-cover"
/>
        <div className="absolute inset-0" style={{ background: 'linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(26,22,18,0.88) 100%)' }} />

        <div className="absolute top-8 left-8 z-10">
          <Link href="/" className="inline-flex items-center gap-2 font-glacial text-[10px] tracking-[0.4em] uppercase hover:opacity-60 transition-opacity" style={{ color: 'rgba(249,246,241,0.6)' }}>
            <ArrowLeft className="w-3.5 h-3.5" /> Home
          </Link>
        </div>

        <div className="absolute bottom-0 left-0 right-0 px-8 sm:px-16 lg:px-24 pb-14 z-10">
          <div className="flex items-center gap-4 mb-4">
            <div className="w-8 h-[1px]" style={{ background: 'rgba(249,246,241,0.35)' }} />
            <span className="font-qlassy text-[10px] tracking-[0.45em] uppercase" style={{ color: 'rgba(249,246,241,0.45)' }}>Get In Touch</span>
          </div>
          <h1 className="font-qlassy font-light" style={{ fontSize: 'clamp(36px, 5vw, 62px)', color: '#F9F6F1', letterSpacing: '-0.01em', lineHeight: 1.1 }}>
            Contact Us
          </h1>
        </div>
      </div>

      {/* ── BODY ── */}
      <div className="max-w-7xl mx-auto px-6 sm:px-10 lg:px-16 py-20">
        <div className="grid grid-cols-1 lg:grid-cols-[1fr_420px] gap-16 xl:gap-28">

          {/* LEFT: Form */}
          <div>
            <div className="flex items-center gap-4 mb-10">
              <div className="w-8 h-[1px]" style={{ background: 'rgba(26,26,26,0.25)' }} />
              <p className="font-qlassy text-[11px] font-bold tracking-[0.5em] uppercase" style={{ color: 'rgba(26,26,26,0.38)' }}>Send a Message</p>
            </div>

            {submitted ? (
              <div className="py-16">
                <div className="w-10 h-[1px] mb-8" style={{ background: 'rgba(26,26,26,0.25)' }} />
                <h2 className="font-qlassy font-light mb-4 text-[32px] tracking-tight">Message received.</h2>
                <p className="font-glacial text-[13px] mb-8 leading-relaxed opacity-60">
                  Thank you for reaching out. We'll get back to you within 24–48 hours.
                </p>
                <button
                  onClick={() => { setSubmitted(false); setForm({ name: '', email: '', subject: '', message: '' }); }}
                  className="flex items-center gap-2 font-glacial text-[10px] tracking-[0.4em] uppercase opacity-50 hover:opacity-100 transition-opacity"
                >
                  <ArrowLeft className="w-3.5 h-3.5" /> Send another
                </button>
              </div>
            ) : (
              <div className="space-y-8 max-w-lg">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-8">
                  <div>
                    <input name="name" type="text" value={form.name} onChange={handleChange}
                      onFocus={() => setFocused('name')} onBlur={() => setFocused('')}
                      placeholder="Full name" style={{ ...inputBase, ...(focused === 'name' ? inputFocused : {}) }} />
                  </div>
                  <div>
                    <input name="email" type="email" value={form.email} onChange={handleChange}
                      onFocus={() => setFocused('email')} onBlur={() => setFocused('')}
                      placeholder="you@email.com" style={{ ...inputBase, ...(focused === 'email' ? inputFocused : {}) }} />
                  </div>
                </div>

                <div>
                  <input name="subject" type="text" value={form.subject} onChange={handleChange}
                    onFocus={() => setFocused('subject')} onBlur={() => setFocused('')}
                    placeholder="How can we help?" style={{ ...inputBase, ...(focused === 'subject' ? inputFocused : {}) }} />
                </div>

                <div>
                  <textarea name="message" value={form.message} onChange={handleChange}
                    onFocus={() => setFocused('message')} onBlur={() => setFocused('')}
                    placeholder="Write your message here..." rows={5}
                    style={{ ...inputBase, resize: 'none', border: `1px solid ${focused === 'message' ? 'rgba(26,26,26,0.4)' : 'rgba(26,26,26,0.18)'}`, padding: '14px', lineHeight: 1.8 }} />
                </div>

                <button
                  onClick={handleSubmit}
                  disabled={sending}
                  className="w-full flex items-center justify-between px-6 py-4 text-[12px] tracking-[0.4em] uppercase font-glacial rounded-sm group relative overflow-hidden"
                  style={{ background: '#1a1a1a', color: '#F9F6F1', opacity: sending ? 0.7 : 1 }}
                >
                  <span className="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-out" style={{ background: '#2e2e2e' }} />
                  <span className="relative z-10">{sending ? 'Sending...' : 'Send Message'}</span>
                  <ArrowRight className="relative z-10 w-4 h-4 transition-transform duration-300 group-hover:translate-x-2" />
                </button>
              </div>
            )}
          </div>

          {/* RIGHT: Info */}
          <div className="space-y-14">
            <div>
              <div className="flex items-center gap-4 mb-8">
                <div className="w-8 h-[1px]" style={{ background: 'rgba(26,26,26,0.25)' }} />
                <p className="font-qlassy text-[11px] font-bold tracking-[0.5em] uppercase" style={{ color: 'rgba(26,26,26,0.38)' }}>Our Details</p>
              </div>

              <ul className="space-y-7">
                {settings.address && (
                  <li className="flex gap-5 items-start">
                    <div className="w-10 h-10 flex items-center justify-center flex-shrink-0" style={{ border: '1px solid rgba(26,26,26,0.12)' }}>
                      <MapPin className="w-3.5 h-3.5 opacity-50" />
                    </div>
                    <p className="font-glacial text-[14px] font-light leading-relaxed whitespace-pre-line opacity-80">{settings.address}</p>
                  </li>
                )}
                {settings.phone && (
                  <li className="flex gap-5 items-center">
                    <div className="w-10 h-10 flex items-center justify-center flex-shrink-0" style={{ border: '1px solid rgba(26,26,26,0.12)' }}>
                      <Phone className="w-3.5 h-3.5 opacity-50" />
                    </div>
                    <a href={`tel:${settings.phone}`} className="font-glacial text-[14px] font-light hover:opacity-50 transition-opacity">{settings.phone}</a>
                  </li>
                )}
                {settings.email && (
                  <li className="flex gap-5 items-center">
                    <div className="w-10 h-10 flex items-center justify-center flex-shrink-0" style={{ border: '1px solid rgba(26,26,26,0.12)' }}>
                      <Mail className="w-3.5 h-3.5 opacity-50" />
                    </div>
                    <a href={`mailto:${settings.email}`} className="font-glacial text-[14px] font-light hover:opacity-50 transition-opacity">{settings.email}</a>
                  </li>
                )}
              </ul>
            </div>

            <div className="h-[1px]" style={{ background: 'rgba(26,26,26,0.08)' }} />

            {/* Socials */}
            <div>
              <div className="flex items-center gap-4 mb-6">
                <div className="w-8 h-[1px]" style={{ background: 'rgba(26,26,26,0.25)' }} />
                <p className="font-qlassy text-[11px] font-bold tracking-[0.5em] uppercase" style={{ color: 'rgba(26,26,26,0.38)' }}>Follow Us</p>
              </div>
              <div className="flex gap-5">
                {socials.map(({ Icon, href }, i) => (
                  <a key={i} href={href || '#'} target="_blank" rel="noreferrer"
                    className="w-9 h-9 flex items-center justify-center transition-all duration-300 opacity-50 hover:opacity-100 hover:border-black"
                    style={{ border: '1px solid rgba(26,26,26,0.12)' }}>
                    <Icon className="w-3.5 h-3.5" />
                  </a>
                ))}
              </div>
            </div>
          </div>
        </div>

        {/* MAP */}
        <div className="mt-24 pt-16 border-t border-black/10">
          <div className="flex items-center gap-4 mb-10">
            <div className="w-8 h-[1px] bg-black/25" />
            <p className="font-qlassy text-[11px] font-bold tracking-[0.5em] uppercase opacity-40">Find Us</p>
          </div>
          <div className="relative w-full h-[400px] bg-black/5 border border-black/10 grayscale contrast-125 opacity-80">
            <iframe
              src={settings.google_map_embed}
              width="100%"
              height="100%"
              style={{ border: 0 }}
              allowFullScreen
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
            />
          </div>
        </div>
      </div>

      <style jsx global>{`
        * { box-sizing: border-box; }
        input::placeholder, textarea::placeholder { color: rgba(16, 16, 16, 0.3); font-family: 'font-glacial'; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #F9F6F1; }
        ::-webkit-scrollbar-thumb { background: rgba(26,26,26,0.12); border-radius: 2px; }
      `}</style>
    </div>
  );
}