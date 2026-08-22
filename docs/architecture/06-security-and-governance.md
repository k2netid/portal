# 06. Security & Governance Standards — Jejakawan Core Engine

Panduan arsitektur keamanan, mitigasi celah, dan kepatuhan sistem di **Jejakawan Core Engine (`ja-core_engine`)**.

---

## 🛡️ 1. Lapisan Keamanan Utama

Jejakawan Core Engine menerapkan prinsip *Defense in Depth* di seluruh tingkatan aplikasi:

```
┌─────────────────────────────────────────────────────────────┐
│ 1. HTTP Layer: Strict CSP (Nonce-based) + HSTS + CORS       │
├─────────────────────────────────────────────────────────────┤
│ 2. Network / Firewall: Dynamic IP Rate Limiting + Allowlist │
├─────────────────────────────────────────────────────────────┤
│ 3. IAM Layer: RBAC (Spatie) + ABAC Policies + WebAuthn/2FA  │
├─────────────────────────────────────────────────────────────┤
│ 4. Data Layer: Prepared Statements + File Sanitization      │
├─────────────────────────────────────────────────────────────┤
│ 5. Audit & Observability: Activity Logs + SIEM Log Exporter │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔒 2. Mekanisme Keamanan Khusus

### A. Content Security Policy (CSP Nonce-based)
- Setiap request HTTP menghasilkan cryptographic nonce unik (`ViteNonceMiddleware`).
- Tag `<script>` dan `<style>` yang diinjeksi ke DOM harus memiliki atribut `nonce="..."`.
- Mencegah serangan *Cross-Site Scripting (XSS)* dan *inline script injection*.

### B. Otentikasi Modern (Passkeys WebAuthn & 2FA TOTP)
- Pengguna dapat mengamankan akun menggunakan biometric Passkeys (FaceID, TouchID, Windows Hello) via standard WebAuthn API (`@laravel/passkeys`).
- Dukungan autentikasi dua faktor (TOTP) berbasis Google Authenticator / Authy.

### C. Sanitasi Upload & SVG Protection
- **Media Upload**: File upload divalidasi MIME type dan extension secara ketat.
- **SVG Sanitizer**: File SVG yang diunggah diproses melalui sanitizer (`enshrined/svg-sanitize`) untuk menghapus elemen berbahaya seperti `<script>`, `onload`, `javascript:`, dan XML external entities (XXE).

### D. Rate Limiting & Bot Prevention
- Endpoint login dan publik dilindungi oleh dynamic sliding-window rate limiter.
- Formulir publik dilengkapi opsi proteksi bot transparan menggunakan reCAPTCHA v3 atau Cloudflare Turnstile token verification dengan backend signature validation.

### E. ABAC (Attribute-Based Access Control)
- Melengkapi RBAC konvensional, modul keamanan memiliki mesin ABAC untuk menegakkan kebijakan berdasarkan konteks pengguna (misal: pembatasan aksi berdasarkan IP subnet, jam kerja, atau status KYC pengguna).

---

## 📊 3. Monitoring & Audit Keamanan

1. **Security Activity Log**:
   - Setiap aksi administratif, percobaan login gagal, perubahan hak akses, atau pengubahan konfigurasi dicatat ke dalam database log aktivitas.
2. **SIEM Exporter**:
   - CMS mendukung ekspor log keamanan real-time ke format standar SIEM (Security Information and Event Management).
3. **Automated Security Audit Workflow**:
   - CI pipeline secara berkala menjalankan `composer audit` dan `npm audit` untuk mendeteksi kerentanan dependensi pihak ketiga.
