import type { RouteLocationNormalizedLoaded } from 'vue-router';
import type { MenuItem } from '@/modules/Layout/types/menu';

export function isExternalLink(url?: string | null): boolean {
  if (!url) return false;
  return url.startsWith('http://') || url.startsWith('https://') || url.startsWith('mailto:') || url.startsWith('tel:');
}

export function normalizePath(raw?: string | null): string {
  if (!raw) return '/';
  if (isExternalLink(raw)) return '';
  const withoutHash = raw.split('#')[0] ?? '';
  const path = withoutHash.startsWith('/') ? withoutHash : `/${withoutHash}`;
  return path !== '/' && path.endsWith('/') ? path.slice(0, -1) : path;
}

export function splitMenuUrl(url?: string | null): { path: string; hash: string } {
  const raw = (url || '/').trim();
  const hashIdx = raw.indexOf('#');
  if (hashIdx === -1) {
    return { path: normalizePath(raw), hash: '' };
  }
  const pathPart = raw.slice(0, hashIdx) || '/';
  const hashPart = raw.slice(hashIdx).toLowerCase();
  return { path: normalizePath(pathPart), hash: hashPart };
}

export function getInternalUrl(url?: string | null): string {
  if (!url) return '/';
  if (isExternalLink(url)) return url;
  if (!url.startsWith('/')) return `/${url}`;
  return url;
}

export function isRoutePathMatch(targetPath: string, currentPath: string): boolean {
  if (!targetPath) return false;
  if (targetPath === '/') return currentPath === '/';
  return currentPath === targetPath || currentPath.startsWith(`${targetPath}/`);
}

export function isDropdownChildActive(
  child: MenuItem,
  siblings: MenuItem[],
  route: RouteLocationNormalizedLoaded,
): boolean {
  const currentPath = normalizePath(route.path);
  const currentHash = (route.hash || '').toLowerCase();
  const { path: childPath, hash: childHash } = splitMenuUrl(child.url);

  if (!childPath) return false;

  const pathMatch = childPath === '/'
    ? currentPath === '/'
    : currentPath === childPath;
  if (!pathMatch) return false;

  if (childHash) {
    return currentHash === childHash;
  }

  if (currentHash) return false;

  const samePathPeers = siblings.filter((s) => {
    const peer = splitMenuUrl(s.url);
    return peer.path === childPath && !peer.hash;
  });
  if (samePathPeers.length <= 1) return true;

  const childKey = String(child.id || child.title || child.url);
  const firstPeer = samePathPeers[0];
  if (!firstPeer) return true;
  const firstKey = String(firstPeer.id || firstPeer.title || firstPeer.url);
  return childKey === firstKey;
}

export function isMenuItemActive(item: MenuItem, route: RouteLocationNormalizedLoaded): boolean {
  const children = Array.isArray(item.children) ? item.children : [];
  if (children.length > 0) {
    return children.some((child) => isDropdownChildActive(child, children, route));
  }

  const currentPath = normalizePath(route.path);
  const currentHash = (route.hash || '').toLowerCase();
  const { path: itemPath, hash: itemHash } = splitMenuUrl(item.url);

  if (itemHash) {
    return currentPath === itemPath && currentHash === itemHash;
  }

  return isRoutePathMatch(itemPath, currentPath) && !currentHash;
}
