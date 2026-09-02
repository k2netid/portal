export type LayungPlanCard = {
  tier: string;
  name: string;
  description: string;
  price: string;
  priceNote?: string;
  speed?: string;
  sla?: string;
  popular?: boolean;
  contactSales?: boolean;
  features: string[];
};

export const LAYUNG_ISP_DEDICATED: LayungPlanCard = {
  tier: 'DIA',
  name: 'Dedicated Internet Access',
  description: 'Koneksi internet dedicated simetris untuk kantor, institusi, dan bisnis yang membutuhkan bandwidth terjamin.',
  price: 'Hubungi Sales',
  priceNote: 'Harga disesuaikan kapasitas & lokasi',
  sla: 'SLA kontrak',
  contactSales: true,
  features: [
    'Bandwidth dedicated simetris (rasio 1:1)',
    'IP publik statis sesuai kebutuhan',
    'Survei lokasi & aktivasi last-mile',
    'Monitoring NOC & eskalasi insiden',
    'Cocok untuk kantor pusat, cabang, dan institusi',
  ],
};

export const LAYUNG_ISP_SOHO_PLANS: LayungPlanCard[] = [
  {
    tier: 'SOHO 50',
    name: 'Broadband Bisnis 50 Mbps',
    description: 'Internet stabil untuk SOHO, ruko, dan usaha kecil dengan kebutuhan cloud & video call rutin.',
    price: 'Mulai Rp 1.200.000',
    priceNote: '+ PPN / bulan',
    speed: 'Up to 50 Mbps',
    sla: 'SLA standar',
    popular: false,
    features: [
      'Up to 50 Mbps untuk operasional harian',
      '1 IP publik statis',
      'Router & instalasi (sesuai paket)',
      'Dukungan teknis jam kerja',
      'Wilayah layanan Bandung & sekitarnya',
    ],
  },
  {
    tier: 'SOHO 100',
    name: 'Broadband Bisnis 100 Mbps',
    description: 'Kapasitas lebih besar untuk kantor menengah, co-working, dan bisnis dengan banyak perangkat aktif.',
    price: 'Mulai Rp 2.000.000',
    priceNote: '+ PPN / bulan',
    speed: 'Up to 100 Mbps',
    sla: 'SLA standar',
    popular: true,
    features: [
      'Up to 100 Mbps untuk tim & perangkat lebih banyak',
      '1–2 IP publik statis',
      'Prioritas bandwidth jam operasional',
      'Dukungan teknis & tiket insiden',
      'Upgrade kapasitas sesuai kebutuhan',
    ],
  },
];

export const LAYUNG_ISP_RETAIL_PLANS: LayungPlanCard[] = [
  {
    tier: 'Paket 10',
    name: 'Retail Broadband 10',
    description: 'Internet rumah tangga dan ritel dengan kebutuhan browsing, streaming, dan belajar daring.',
    price: 'Rp 150.000',
    priceNote: '+ PPN / bulan',
    speed: '10 Mbps (up to 15 Mbps)',
    popular: false,
    features: [
      'Cocok untuk 2–5 perangkat aktif',
      'Instalasi standar area coverage',
      'Pembayaran bulanan',
      'Area layanan sesuai jangkauan jaringan K2NET',
    ],
  },
  {
    tier: 'Paket 15',
    name: 'Retail Broadband 15',
    description: 'Pilihan menengah untuk keluarga atau usaha rumahan dengan aktivitas daring lebih intens.',
    price: 'Rp 200.000',
    priceNote: '+ PPN / bulan',
    speed: '15 Mbps (up to 20 Mbps)',
    popular: true,
    features: [
      'Cocok untuk 5–8 perangkat aktif',
      'Streaming & video call lebih lancar',
      'Instalasi standar area coverage',
      'Pembayaran bulanan',
    ],
  },
  {
    tier: 'Paket 20',
    name: 'Retail Broadband 20',
    description: 'Paket retail tertinggi untuk kebutuhan multi-perangkat dan aktivitas online sehari-hari.',
    price: 'Rp 250.000',
    priceNote: '+ PPN / bulan',
    speed: '20 Mbps (up to 25 Mbps)',
    popular: false,
    features: [
      'Cocok untuk 8–12 perangkat aktif',
      'Kapasitas lebih lega untuk WFH & UMKM',
      'Instalasi standar area coverage',
      'Pembayaran bulanan',
    ],
  },
];

export const LAYUNG_MSP_PLANS: LayungPlanCard[] = [
  {
    tier: 'MS Basic',
    name: 'Managed Services — Basic',
    description: 'Pendampingan IT operasional dasar untuk sekolah dan institusi dengan kebutuhan jaringan rutin.',
    price: 'Hubungi Sales',
    priceNote: 'Paket disesuaikan jumlah node & lokasi',
    contactSales: true,
    features: [
      'Instalasi jaringan & dokumentasi dasar',
      'Perawatan jaringan berkala (kunjungan terjadwal)',
      'Bantuan remote jam kerja via Service Desk',
      'Instalasi perangkat jaringan & akses WiFi',
      'Tiket support & eskalasi insiden standar',
    ],
  },
  {
    tier: 'MS Standard',
    name: 'Managed Services — Standard',
    description: 'Cakupan lebih lengkap untuk sekolah menengah, kompleks pendidikan, dan institusi dengan infrastruktur IT aktif.',
    price: 'Hubungi Sales',
    priceNote: 'Termasuk server, aplikasi, dan CCTV',
    contactSales: true,
    popular: true,
    features: [
      'Semua layanan paket Basic',
      'Instalasi & perawatan server serta aplikasi',
      'Instalasi & perawatan CCTV',
      'Pendampingan kegiatan berbasis IT (ujian, acara sekolah)',
      'Monitoring jaringan dasar & laporan berkala',
    ],
  },
  {
    tier: 'MS Custom',
    name: 'Managed Services — Custom',
    description: 'Ruang lingkup disesuaikan kontrak untuk sekolah besar, yayasan pendidikan, atau multi-lokasi.',
    price: 'Hubungi Sales',
    priceNote: 'SLA & jam respon kustom',
    contactSales: true,
    features: [
      'Semua layanan paket Standard',
      'Multi-gedung, lab komputer, dan proyek khusus',
      'SLA, jam respon, dan engineer on-call kustom',
      'Pekerjaan IT lainnya sesuai kebutuhan institusi',
      'Dedicated account manager & perencanaan tahunan',
    ],
  },
];
