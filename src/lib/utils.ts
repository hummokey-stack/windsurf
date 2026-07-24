/**
 * Utility functions for GlisseShop Astro
 */

export function slugify(text: string): string {
  if (!text) return '';
  
  const charMap: Record<string, string> = {
    'à': 'a', 'â': 'a', 'ä': 'a', 'á': 'a', 'ã': 'a',
    'è': 'e', 'é': 'e', 'ê': 'e', 'ë': 'e',
    'ì': 'i', 'î': 'i', 'ï': 'i', 'í': 'i',
    'ò': 'o', 'ô': 'o', 'ö': 'o', 'ó': 'o', 'õ': 'o',
    'ù': 'u', 'û': 'u', 'ü': 'u', 'ú': 'u',
    'ç': 'c', 'ñ': 'n', 'ý': 'y', 'ÿ': 'y',
    '&': 'et',
  };

  let str = text.toLowerCase();
  for (const [char, replacement] of Object.entries(charMap)) {
    str = str.replaceAll(char, replacement);
  }

  return str
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/[\s-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export function formatPrix(prix: number): string {
  const formatted = new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(prix || 0);

  return `${formatted} €`;
}

export function calculReduction(ancien: number, nouveau: number): number {
  if (!ancien || ancien <= 0 || !nouveau || nouveau >= ancien) return 0;
  return Math.round(((ancien - nouveau) / ancien) * 100);
}

export function imageProduit(imagePath?: string | null): string {
  if (!imagePath || imagePath.trim() === '') {
    return '/assets/images/placeholder.svg';
  }
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath;
  }
  if (imagePath.startsWith('/')) {
    return imagePath;
  }
  return `/${imagePath}`;
}

export function genererReference(): string {
  const randomHex = Math.random().toString(36).substring(2, 8).toUpperCase();
  return `CMD-${randomHex}`;
}
