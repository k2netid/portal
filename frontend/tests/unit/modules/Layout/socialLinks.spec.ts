import { describe, expect, it } from 'vitest';
import { parseSocialLinks } from '@/modules/Layout/utils/socialLinks';

describe('parseSocialLinks', () => {
  it('returns empty array when input is null, undefined, or empty string', () => {
    expect(parseSocialLinks(null)).toEqual([]);
    expect(parseSocialLinks(undefined)).toEqual([]);
    expect(parseSocialLinks('')).toEqual([]);
    expect(parseSocialLinks('   ')).toEqual([]);
  });

  it('returns empty array for invalid JSON strings or non-array JSON', () => {
    expect(parseSocialLinks('invalid-json')).toEqual([]);
    expect(parseSocialLinks('{"icon": "Github"}')).toEqual([]);
    expect(parseSocialLinks(12345)).toEqual([]);
    expect(parseSocialLinks(true)).toEqual([]);
  });

  it('correctly parses JSON string array of social links', () => {
    const json = JSON.stringify([
      { icon: 'Github', label: 'GitHub', url: 'https://github.com/jejakawan' },
      { icon: 'Linkedin', label: 'LinkedIn', url: 'https://linkedin.com/company/jejakawan' },
    ]);

    const result = parseSocialLinks(json);
    expect(result).toHaveLength(2);
    expect(result[0]).toEqual({
      icon: 'Github',
      label: 'GitHub',
      url: 'https://github.com/jejakawan',
    });
    expect(result[1]).toEqual({
      icon: 'Linkedin',
      label: 'LinkedIn',
      url: 'https://linkedin.com/company/jejakawan',
    });
  });

  it('correctly handles native array of social link objects', () => {
    const raw = [
      { network: 'x', url: 'https://x.com/jejakawan' },
      { platform: 'youtube', url: 'https://youtube.com' },
    ];

    const result = parseSocialLinks(raw);
    expect(result).toHaveLength(2);
    expect(result[0]?.icon).toBe('x');
    expect(result[0]?.url).toBe('https://x.com/jejakawan');
    expect(result[1]?.icon).toBe('youtube');
    expect(result[1]?.url).toBe('https://youtube.com');
  });

  it('filters out non-object items in array', () => {
    const raw = [null, 'a string', 123, { icon: 'Mail', url: 'mailto:test@example.com' }];
    const result = parseSocialLinks(raw);
    expect(result).toHaveLength(1);
    expect(result[0]).toEqual({
      icon: 'Mail',
      url: 'mailto:test@example.com',
    });
  });
});
