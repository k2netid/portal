import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Utility function to merge Tailwind CSS classes
 * Combines clsx for conditional classes and twMerge for deduplication
 * 
 * @param inputs - Classes to merge
 * @returns {string} Merged class string
 * 
 * @example
 * cn('px-4 py-2', 'bg-blue-500', { 'text-white': true })
 * // Returns: 'px-4 py-2 bg-blue-500 text-white'
 */
export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}
