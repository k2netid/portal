export interface SocialLinkItem {
  icon?: string;
  url?: string;
  label?: string;
  network?: string;
  platform?: string;
}

/**
 * Safely parse social links whether provided as an Array, a JSON-encoded string,
 * or null/undefined. Guarantees an array output to prevent TypeError on .map().
 */
export function parseSocialLinks(raw: unknown): SocialLinkItem[] {
  if (!raw) {
    return [];
  }

  let list: unknown = raw;

  if (typeof raw === 'string') {
    const trimmed = raw.trim();
    if (!trimmed || trimmed === '[]' || trimmed === '{}') {
      return [];
    }
    try {
      list = JSON.parse(trimmed);
    } catch {
      return [];
    }
  }

  if (!Array.isArray(list)) {
    return [];
  }

  return list
    .filter((item): item is Record<string, unknown> => typeof item === 'object' && item !== null)
    .map((item) => {
      const icon = String(item.icon || item.network || item.platform || 'Globe');
      const url = typeof item.url === 'string' ? item.url : '';
      const label = typeof item.label === 'string' ? item.label : undefined;
      const network = typeof item.network === 'string' ? item.network : undefined;
      const platform = typeof item.platform === 'string' ? item.platform : undefined;

      return {
        icon,
        url,
        ...(label ? { label } : {}),
        ...(network ? { network } : {}),
        ...(platform ? { platform } : {}),
      };
    });
}
