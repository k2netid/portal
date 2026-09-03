import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';

// Custom Metrics
const successfulRequests = new Counter('k2net_successful_requests');
const errorRate = new Rate('k2net_error_rate');
const pageLatency = new Trend('k2net_page_latency');

export const options = {
  // Ramp-up up to 5,000 - 10,000 concurrent users
  stages: [
    { duration: '30s', target: 500 },    // Warm-up ke 500 VUs
    { duration: '1m',  target: 2000 },   // Naik ke 2.000 VUs
    { duration: '2m',  target: 5000 },   // Tekan di 5.000 VUs (Sustained Load)
    { duration: '1m',  target: 10000 },  // Spike Peak ke 10.000 VUs
    { duration: '1m',  target: 1000 },   // Cool-down ke 1.000 VUs
    { duration: '30s', target: 0 },      // Kembali ke 0
  ],
  thresholds: {
    // 95% request harus selesai di bawah 1.5 detik
    http_req_duration: ['p(95)<1500', 'p(99)<3000'],
    // Tingkat kegagalan (error rate) harus di bawah 5%
    'k2net_error_rate': ['rate<0.05'],
    'http_req_failed': ['rate<0.05'],
  },
};

const BASE_URL = __ENV.TARGET_URL || 'http://127.0.0.1:8083';

export default function () {
  const headers = {
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'User-Agent': 'k6-load-test/k2net-portal',
  };

  // 1. Browsing Landing Page
  group('1. Visit Landing Page', () => {
    const res = http.get(`${BASE_URL}/`, { headers });
    const ok = check(res, {
      'status is 200': (r) => r.status === 200,
      'has K2NET branding': (r) => r.body.includes('K2NET') || r.body.includes('k2net'),
    });
    errorRate.add(!ok);
    if (ok) successfulRequests.add(1);
    pageLatency.add(res.timings.duration);
  });

  sleep(1);

  // 2. Browsing ISP Pricing Page
  group('2. Visit /pricing/isp', () => {
    const res = http.get(`${BASE_URL}/pricing/isp`, { headers });
    const ok = check(res, {
      'status is 200': (r) => r.status === 200,
      'body received': (r) => r.body && r.body.length > 500,
    });
    errorRate.add(!ok);
    if (ok) successfulRequests.add(1);
    pageLatency.add(res.timings.duration);
  });

  sleep(1);

  // 3. Browsing Contact Reach Form
  group('3. Visit /contact', () => {
    const res = http.get(`${BASE_URL}/contact`, { headers });
    const ok = check(res, {
      'status is 200': (r) => r.status === 200,
    });
    errorRate.add(!ok);
    if (ok) successfulRequests.add(1);
    pageLatency.add(res.timings.duration);
  });

  sleep(2);
}
