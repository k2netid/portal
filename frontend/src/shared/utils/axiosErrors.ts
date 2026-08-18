import { isCancel, isAxiosError } from 'axios';

/** True when the request was cancelled/aborted (navigation, unmount, tab switch). */
export function isRequestAborted(error: unknown): boolean {
  if (isCancel(error)) return true;
  if (!isAxiosError(error)) return false;
  const code = error.code;
  const msg = (error.message || '').toLowerCase();
  return code === 'ERR_CANCELED' || code === 'ECONNABORTED' || msg.includes('abort') || msg.includes('canceled');
}
