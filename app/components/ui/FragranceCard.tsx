import Image from 'next/image';
// import Icon from '../ui/Icon';
import { ShoppingBag } from 'lucide-react';

interface FragranceCardProps {
  name: string;
  type: string;
  image: string;
}

export default function FragranceCard({ name, type, image }: FragranceCardProps) {
  return (
    <div className="group cursor-pointer">
      <div className="aspect-[4/5] bg-[#F3F1ED] dark:bg-zinc-900 overflow-hidden relative mb-4">
        <Image
          src={image}
          alt={name}
          fill
          className="object-cover transition-transform duration-700"
        />
      </div>
      <div className="flex justify-between items-end border-b border-black/10 dark:border-white/10 pb-4">
        <div>
          <p className="text-xs uppercase tracking-widest mb-1 text-gray-muted">{type}</p>
          <h4 className="text-2xl font-display">{name}</h4>
        </div>
        <button className="p-2 rounded-full hover:bg-black hover:text-white transition-all duration-300">
    <ShoppingBag size={18} />
  </button>
      </div>
    </div>
  );
}
