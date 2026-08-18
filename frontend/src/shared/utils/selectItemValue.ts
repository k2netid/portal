/** Sentinel for Radix SelectItem — empty string is reserved to clear the Select. */
export const SELECT_ITEM_EMPTY = '__select_item_empty__';

export function toSelectItemValue(raw: unknown): string {
  if (raw === null || raw === undefined) return SELECT_ITEM_EMPTY;
  const s = String(raw);
  return s === '' ? SELECT_ITEM_EMPTY : s;
}

export function fromSelectItemValue(value: string | undefined): string {
  if (value === undefined || value === SELECT_ITEM_EMPTY) return '';
  return value;
}

export function isEmptySelectItemValue(value: unknown): boolean {
  return value === '' || value === null || value === undefined
    || (typeof value === 'string' && value.trim() === '');
}
