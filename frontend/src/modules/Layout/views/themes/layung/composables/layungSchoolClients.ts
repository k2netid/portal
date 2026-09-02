export type LayungSchoolClient = {
  id: number;
  name: string;
  short: string;
  city: string;
};

/** Demo roster: SMP Negeri 1–57 Kota Bandung. Replace with live CMS data when available. */
export function buildBandungSmpnClients(count = 57): LayungSchoolClient[] {
  return Array.from({ length: count }, (_, index) => {
    const n = index + 1;
    return {
      id: n,
      name: `SMP Negeri ${n} Kota Bandung`,
      short: `SMPN ${n}`,
      city: 'Bandung',
    };
  });
}

export const LAYUNG_DEMO_SCHOOL_NOTES: Array<{
  schoolId: number;
  role: string;
  quote: string;
}> = [
  {
    schoolId: 1,
    role: 'Koordinator IT',
    quote: 'Jaringan kampus dan lab komputer lebih teratur setelah instalasi dan perawatan rutin. Tiket gangguan ditangani lewat Service Desk, bukan menunggu teknisi dadakan.',
  },
  {
    schoolId: 15,
    role: 'Staf TU / IT',
    quote: 'Saat ujian berbasis komputer, koneksi internet sekolah tetap dipakai bersama CCTV dan administrasi. Pendampingan K2NET membantu kami siap sebelum hari H.',
  },
  {
    schoolId: 42,
    role: 'Wakil Kepala Sekolah',
    quote: 'Server aplikasi sekolah dan kamera CCTV dirawat dalam satu kontrak managed services. Lingkup pekerjaan jelas, tidak perlu tim IT internal penuh.',
  },
];
