import { describe, it, expect } from 'vitest';
import { parseResponse, parseSingleResponse, parsePagination, ensureArray } from '@/shared/utils/responseParser';
import type { AxiosResponse } from 'axios';

describe('responseParser util', () => {
    describe('parseResponse', () => {
        it('handles empty response data', () => {
            const resp = { data: null } as unknown as AxiosResponse;
            const res = parseResponse(resp);
            expect(res.data).toEqual([]);
            expect(res.pagination).toBeNull();
        });

        it('handles direct array data', () => {
            const resp = { data: [{ id: "1" }, { id: "2" }] } as unknown as AxiosResponse;
            const res = parseResponse(resp);
            expect(res.data).toHaveLength(2);
            expect((res.data[0] as any).id).toBe('1');
        });

        it('handles data wrapped in items with pagination', () => {
            const resp = {
                data: {
                    items: [{ id: "1" }],
                    pagination: { current_page: 1, last_page: 2, from: 1, to: 10, total: 20 }
                }
            } as unknown as AxiosResponse;

            const res = parseResponse(resp);
            expect(res.data).toHaveLength(1);
            expect(res.pagination?.total).toBe(20);
        });

        it('handles data wrapped in inner data.data array', () => {
            const resp = {
                data: {
                    data: {
                        data: [{ id: "1" }],
                        current_page: 1, last_page: 1, from: 1, to: 1, total: 1, per_page: 15
                    }
                }
            } as unknown as AxiosResponse;

            const res = parseResponse(resp);
            expect(res.data).toHaveLength(1);
            expect(res.pagination?.total).toBe(1);
        });

        it('handles single object in data wrapper', () => {
            const resp = {
                data: {
                    data: { id: "42" }
                }
            } as unknown as AxiosResponse;

            const res = parseResponse(resp);
            expect(res.data).toHaveLength(1);
            expect((res.data[0] as any).id).toBe('42');
        });

        it('handles array in data wrapper', () => {
            const resp = {
                data: {
                    data: [{ id: "42" }],
                    meta: { pagination: { current_page: 1, last_page: 2, from: 1, to: 1, total: 2 } }
                }
            } as unknown as AxiosResponse;
            const res = parseResponse(resp);
            expect(res.data).toHaveLength(1);
            expect(res.pagination?.total).toBe(2);
        });
    });

    describe('parseSingleResponse', () => {
        it('returns null for empty data', () => {
            expect(parseSingleResponse({ data: null } as unknown as AxiosResponse)).toBeNull();
        });

        it('extracts nested data object', () => {
            expect(parseSingleResponse({ data: { data: { id: "1" } } } as unknown as AxiosResponse)).toEqual({ id: "1" });
        });

        it('returns direct data object', () => {
            expect(parseSingleResponse({ data: { id: "2" } } as unknown as AxiosResponse)).toEqual({ id: "2" });
        });
    });

    describe('parsePagination', () => {
        it('returns null for bad data', () => {
            expect(parsePagination({ data: null } as unknown as AxiosResponse)).toBeNull();
        });

        it('returns nested pagination', () => {
            expect(parsePagination({ data: { data: { pagination: { total: 10 } } } } as unknown as AxiosResponse)).toEqual({ total: 10 });
        });

        it('returns direct pagination', () => {
            expect(parsePagination({ data: { pagination: { total: 15 } } } as unknown as AxiosResponse)).toEqual({ total: 15 });
        });

        it('constructs pagination from direct properties', () => {
            const resp = {
                data: { current_page: 2, last_page: 5, from: 11, to: 20, total: 50 }
            } as unknown as AxiosResponse;
            const res = parsePagination(resp);
            expect(res?.current_page).toBe(2);
            expect(res?.total).toBe(50);
        });

        it('returns null for object without current_page', () => {
            const resp = {
                data: { foo: 'bar' }
            } as unknown as AxiosResponse;
            expect(parsePagination(resp)).toBeNull();
        });
    });

    describe('ensureArray', () => {
        it('returns array as is', () => {
            expect(ensureArray([1, 2])).toEqual([1, 2]);
        });
        it('returns empty array if not array', () => {
            expect(ensureArray('string')).toEqual([]);
            expect(ensureArray(null)).toEqual([]);
            expect(ensureArray(undefined)).toEqual([]);
            expect(ensureArray({})).toEqual([]);
        });
    });
});
