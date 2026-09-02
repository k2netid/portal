export type BandwidthSegment = 'auto' | 'retail' | 'soho' | 'dia';
export type BandwidthWorkload = 'standard' | 'video' | 'cloud' | 'heavy';
export type BandwidthPlanId = 'retail-10' | 'retail-15' | 'retail-20' | 'soho-50' | 'soho-100' | 'dia';

export const WORKLOAD_MBPS: Record<BandwidthWorkload, number> = {
  standard: 2,
  video: 4.5,
  cloud: 6.5,
  heavy: 10,
};

export const HEADROOM = 1.25;

/** Internal planning knobs — not a third-party standard. */
export function concurrencyRatio(userCount: number): number {
  const n = userCount;
  if (n <= 10) return 0.85;
  if (n <= 30) return 0.75;
  if (n <= 70) return 0.65;
  if (n <= 150) return 0.55;
  return 0.45;
}

export function activeConcurrentUsers(userCount: number): number {
  return Math.max(1, Math.round(userCount * concurrencyRatio(userCount)));
}

export function estimatePeakMbps(userCount: number, mbpsPerUser: number): number {
  const rawLoad = activeConcurrentUsers(userCount) * mbpsPerUser;
  return Math.max(10, Math.round(rawLoad * HEADROOM));
}

export function diaSpeedLabel(mbps: number): string {
  if (mbps >= 1000) return `${(mbps / 1000).toFixed(1)} Gbps`;
  return `${Math.max(50, Math.ceil(mbps / 25) * 25)} Mbps`;
}

export function recommendPlanId(input: {
  segment: BandwidthSegment;
  userCount: number;
  mbps: number;
  workload: BandwidthWorkload;
}): BandwidthPlanId {
  const { segment, userCount: count, mbps, workload } = input;

  if (segment === 'retail') {
    if (count <= 5 || mbps <= 14) return 'retail-10';
    if (count <= 8 || mbps <= 22) return 'retail-15';
    return 'retail-20';
  }

  if (segment === 'soho') {
    if (count <= 35 && mbps <= 65) return 'soho-50';
    return 'soho-100';
  }

  if (segment === 'dia') return 'dia';

  if (count <= 5 && mbps <= 14) return 'retail-10';
  if (count <= 8 && mbps <= 22) return 'retail-15';
  if (count <= 15 && mbps <= 35) return 'retail-20';
  if (count <= 35 && mbps <= 70 && workload !== 'heavy') return 'soho-50';
  if (count <= 65 && mbps <= 120 && workload !== 'heavy') return 'soho-100';
  return 'dia';
}
