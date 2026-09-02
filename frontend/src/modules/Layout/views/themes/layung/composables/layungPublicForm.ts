export type PublicFormField = {
  name: string;
  label: string;
  type: string;
  placeholder?: string | null;
  help_text?: string | null;
  options: unknown;
  is_required: boolean;
};

export type PublicFormDefinition = {
  id: string;
  slug: string;
  name: string;
  description?: string | null;
  success_message?: string | null;
  redirect_url?: string;
  settings: {
    captcha_required: boolean;
    email_notifications?: boolean;
  };
  fields: PublicFormField[];
};

export function publicFormSubmitPath(slug: string): string {
  return `/public/forms/${encodeURIComponent(slug)}/submit`;
}

export function publicFormTrackPath(slug: string): string {
  return `/public/forms/${encodeURIComponent(slug)}/track`;
}

export type FormRedirectKind = 'in-app' | 'absolute' | 'ignore';

export function classifyFormRedirect(raw: string | null | undefined): { kind: FormRedirectKind; url: string } {
  const url = (raw ?? '').trim();
  if (!url) {
    return { kind: 'ignore', url: '' };
  }
  if (url.startsWith('/') && !url.startsWith('//')) {
    return { kind: 'in-app', url };
  }
  if (/^https?:\/\//i.test(url)) {
    return { kind: 'absolute', url };
  }
  return { kind: 'ignore', url };
}

export function buildPublicFormSubmitPayload(
  fields: PublicFormField[] | undefined,
  values: Record<string, unknown>,
): Record<string, unknown> {
  const out: Record<string, unknown> = {};
  if (!fields?.length) {
    return out;
  }
  for (const field of fields) {
    if (field.type === 'file' || field.type === 'image') {
      continue;
    }
    const key = field.name;
    const val = values[key];

    if (field.type === 'multiselect' || field.type === 'checkbox') {
      out[key] = Array.isArray(val) ? val : [];
      continue;
    }
    if (field.type === 'boolean') {
      out[key] = !!val;
      continue;
    }

    const isEmpty = val === '' || val === null || val === undefined;
    if (isEmpty) {
      if (field.is_required) {
        out[key] = '';
      }
      continue;
    }

    if (field.type === 'number' && typeof val === 'string') {
      const n = Number(val);
      out[key] = Number.isNaN(n) ? val : n;
      continue;
    }

    out[key] = val;
  }
  return out;
}

export function appendPayloadToFormData(fd: FormData, body: Record<string, unknown>): void {
  for (const [key, val] of Object.entries(body)) {
    if (val === null || val === undefined) {
      continue;
    }
    if (Array.isArray(val)) {
      for (const item of val) {
        fd.append(`${key}[]`, String(item));
      }
      continue;
    }
    if (typeof val === 'boolean') {
      fd.append(key, val ? '1' : '0');
      continue;
    }
    if (typeof val === 'number') {
      fd.append(key, String(val));
      continue;
    }
    fd.append(key, String(val));
  }
}

export function mapPublicFormValidationErrors(data: unknown): Record<string, string> {
  if (!data || typeof data !== 'object') {
    return {};
  }
  const errs = (data as { errors?: Record<string, string[] | string> }).errors;
  if (!errs || typeof errs !== 'object') {
    return {};
  }
  const out: Record<string, string> = {};
  for (const [k, v] of Object.entries(errs)) {
    if (Array.isArray(v) && v[0]) {
      out[k] = String(v[0]);
    } else if (typeof v === 'string') {
      out[k] = v;
    }
  }
  return out;
}
