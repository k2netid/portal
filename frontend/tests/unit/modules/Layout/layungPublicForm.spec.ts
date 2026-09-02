import { describe, expect, it } from 'vitest';
import {
    appendPayloadToFormData,
    buildPublicFormSubmitPayload,
    classifyFormRedirect,
    mapPublicFormValidationErrors,
    publicFormSubmitPath,
} from '@/modules/Layout/views/themes/layung/composables/layungPublicForm';

describe('layung public form helpers', () => {
    it('posts JSON to the published form submit route', () => {
        expect(publicFormSubmitPath('contact')).toBe('/public/forms/contact/submit');
        expect(publicFormSubmitPath('sales form')).toBe('/public/forms/sales%20form/submit');
    });

    it('builds a payload that skips files and coerces numbers', () => {
        const payload = buildPublicFormSubmitPayload(
            [
                { name: 'email', label: 'Email', type: 'email', options: null, is_required: true },
                { name: 'qty', label: 'Qty', type: 'number', options: null, is_required: false },
                { name: 'topics', label: 'Topics', type: 'multiselect', options: [], is_required: false },
                { name: 'cv', label: 'CV', type: 'file', options: null, is_required: false },
                { name: 'empty', label: 'Empty', type: 'text', options: null, is_required: false },
            ],
            { email: 'a@b.c', qty: '12', topics: ['noc'], cv: null, empty: '' },
        );
        expect(payload).toEqual({
            email: 'a@b.c',
            qty: 12,
            topics: ['noc'],
        });
    });

    it('keeps required empty fields so the API can 422 them', () => {
        expect(
            buildPublicFormSubmitPayload(
                [{ name: 'name', label: 'Name', type: 'text', options: null, is_required: true }],
                { name: '' },
            ),
        ).toEqual({ name: '' });
    });

    it('only follows in-app paths or http(s) redirects', () => {
        expect(classifyFormRedirect('/thanks')).toEqual({ kind: 'in-app', url: '/thanks' });
        expect(classifyFormRedirect('https://k2net.id/ok')).toEqual({
            kind: 'absolute',
            url: 'https://k2net.id/ok',
        });
        expect(classifyFormRedirect('//evil.example/phish').kind).toBe('ignore');
        expect(classifyFormRedirect('javascript:alert(1)').kind).toBe('ignore');
        expect(classifyFormRedirect('').kind).toBe('ignore');
    });

    it('flattens laravel-style field errors', () => {
        expect(
            mapPublicFormValidationErrors({
                errors: { email: ['wajib diisi'], name: 'terlalu pendek' },
            }),
        ).toEqual({ email: 'wajib diisi', name: 'terlalu pendek' });
    });

    it('appends arrays and booleans onto FormData', () => {
        const fd = new FormData();
        appendPayloadToFormData(fd, { ok: true, tags: ['a', 'b'], n: 3 });
        expect(fd.get('ok')).toBe('1');
        expect(fd.getAll('tags[]')).toEqual(['a', 'b']);
        expect(fd.get('n')).toBe('3');
    });
});
