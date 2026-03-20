import Image from 'next/image';
import Icon from '../ui/Icon';

interface ProductCardProps {
  name: string;
  category: string;
  image: string;
  price: string;
  rating?: number;
  reviews?: number;
}

export default function ProductCard({ 
  name, 
  category, 
  image, 
  price,
  rating = 5,
  reviews = 0
}: ProductCardProps) {
  return (
    <div className="text-center group cursor-pointer">
      <div className="mb-6 bg-white dark:bg-zinc-900 p-8">
        <div className="relative w-full aspect-[3/4]">
          <Image
            src={image}
            alt={name}
            fill
            className="object-contain group-hover:scale-105 transition-transform duration-500"
          />
        </div>
      </div>
      <h4 className="font-display text-lg mb-1">{name}</h4>
      {reviews > 0 && (
        <div className="flex justify-center text-xs text-accent-gold mb-2">
          {[...Array(5)].map((_, i) => (
            <Icon
              key={i}
              name={i < Math.floor(rating) ? 'star' : i < rating ? 'star_half' : 'star_outline'}
              className="text-sm"
            />
          ))}
          <span className="ml-1 text-gray-muted">({reviews})</span>
        </div>
      )}
      <p className="text-xs font-semibold tracking-widest">{price}</p>
    </div>
  );
}
