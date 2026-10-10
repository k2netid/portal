# Changelog — Billing

All notable changes to the **Billing** module.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added
- Member self-billing and subscription lifecycle integration with `ja-cp` control plane.
- Integration endpoints: `GET /api/v1/member/billing`, `GET /api/v1/member/billing/packages`, `POST /api/v1/member/billing/order`, `POST /api/v1/member/billing/checkout/{transactionId}`, and `POST /api/v1/public/billing/webhook`.
- Member portal UI views and components for viewing active subscription, plan selection, and invoice payments.
