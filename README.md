# GRAY - Luxury Fragrance & Skincare Website

A modern, elegant e-commerce website built with Next.js 14, TypeScript, and Tailwind CSS.

## Features

- 🎨 **Modern Design**: Sophisticated luxury brand aesthetic with smooth animations
- 🎬 **Video Hero**: Full-screen video background support with image fallback
- 🌓 **Dark Mode Ready**: Full dark mode support with smooth transitions
- 📱 **Responsive**: Mobile-first design that looks great on all devices
- ⚡ **Fast**: Built on Next.js 14 App Router for optimal performance
- 🎯 **Type Safe**: Fully typed with TypeScript
- 🧩 **Component-Based**: Reusable, modular components

## Getting Started

### Installation

1. Install dependencies:
```bash
npm install
```

2. **Add your hero video** (IMPORTANT):
   - Place your video file as `hero-video.mp4` in the `public/` folder
   - Or update the video path in `app/page.tsx`

3. Run the development server:
```bash
npm run dev
```

4. Open [http://localhost:3000](http://localhost:3000)

## Hero Video Setup

The hero section supports video backgrounds:

1. **Add video to public folder**: `/public/hero-video.mp4`
2. **Customize in `app/page.tsx`:**
```tsx
<HeroSection 
  videoUrl="/hero-video.mp4"           // Your video path
  posterImage="https://..."            // Fallback image
/>
```

**Recommended video specs:**
- Format: MP4 (H.264)
- Resolution: 1920x1080 or higher
- File size: Keep under 5MB for fast loading
- Duration: 10-30 seconds (it will loop)

## Project Structure

```
gray-luxury-nextjs/
├── app/
│   ├── components/
│   │   ├── layout/          # Navigation, Footer
│   │   ├── sections/        # Hero, Products, etc.
│   │   └── ui/             # Button, Card, Icon
│   ├── globals.css
│   ├── layout.tsx
│   └── page.tsx
├── public/                 # Put hero-video.mp4 here!
├── tailwind.config.ts
└── package.json
```

## Customization

### Colors
Edit `tailwind.config.ts`:
- `primary`, `accent-gold`, `background-light/dark`

### Content
- Products: `ProductsShowcase.tsx`, `FeaturedFragrances.tsx`
- Testimonials: `TestimonialsSection.tsx`
- Hero video: `page.tsx`

## Tech Stack

- Next.js 14 (App Router)
- TypeScript
- Tailwind CSS
- Google Material Symbols
- HTML5 Video

## License

Educational template project.
