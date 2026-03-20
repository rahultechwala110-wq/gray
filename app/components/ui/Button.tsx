import { ReactNode } from 'react';

interface ButtonProps {
  children: ReactNode;
  variant?: 'primary' | 'secondary' | 'outline';
  href?: string;
  className?: string;
  onClick?: () => void;
}

export default function Button({ 
  children, 
  variant = 'outline', 
  href, 
  className = '',
  onClick 
}: ButtonProps) {
  const baseStyles = "inline-flex items-center justify-center gap-4 px-10 py-4 text-xs tracking-[0.3em] uppercase transition-all duration-500";
  
  const variants = {
    primary: "bg-primary dark:bg-white text-white dark:text-black hover:bg-primary/90",
    secondary: "bg-accent-gold text-white hover:bg-accent-gold/90",
    outline: "border border-primary dark:border-white hover:bg-primary hover:text-white dark:hover:bg-white dark:hover:text-black"
  };

  const styles = `${baseStyles} ${variants[variant]} ${className}`;

  if (href) {
    return (
      <a href={href} className={styles}>
        {children}
      </a>
    );
  }

  return (
    <button onClick={onClick} className={styles}>
      {children}
    </button>
  );
}
