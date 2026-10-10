import apiClient from '@/engine/api/client';
import type { AxiosResponse } from 'axios';

export interface BillingPackage {
    id: string;
    product_id?: string;
    product_name?: string;
    name: string;
    price_monthly: number;
    price_yearly: number;
    user_limit: number;
    storage_limit_mb: number;
    ai_monthly_token_limit?: number;
    feature_highlights?: string[];
    features?: Record<string, unknown>;
}

export interface BillingTransaction {
    id: string;
    invoice_number: string;
    payment_status: 'pending' | 'paid' | 'cancel' | 'expire' | 'refunded' | string;
    payment_method?: string;
    amount: number;
    amount_formatted?: string;
    package_name?: string;
    paid_at?: string;
    created_at?: string;
    can_checkout?: boolean;
}

export interface BillingSubscription {
    id: string;
    name: string;
    domain?: string;
    status: 'active' | 'pending' | 'trial' | 'suspended' | 'expired' | string;
    expires_at?: string;
    user_limit: number;
    storage_limit_mb: number;
}

export interface BillingSummaryResponse {
    has_subscription: boolean;
    subscription: BillingSubscription | null;
    package: {
        id?: string;
        name: string;
        price_monthly?: number;
        price_yearly?: number;
        feature_highlights?: string[];
    } | null;
    transactions: BillingTransaction[];
    pending_count: number;
    error?: string;
}

export interface OrderSubscriptionPayload {
    package_id: string;
    billing_period?: 'monthly' | 'yearly';
    payment_method?: 'midtrans' | 'xendit' | 'manual';
    phone?: string;
    domain?: string;
    notes?: string;
}

export interface OrderSubscriptionResult {
    subscription: BillingSubscription;
    package: { id: string; name: string; price: number; billing_period: string };
    transaction: BillingTransaction;
    checkout?: {
        mode?: string;
        provider?: string;
        invoice_number?: string;
        redirect_url?: string | null;
        client_token?: string | null;
        billing_period?: string;
        message?: string;
    };
}

export const billingService = {
    fetchBilling(): Promise<AxiosResponse<{ success: boolean; data: BillingSummaryResponse }>> {
        return apiClient.get('/member/billing');
    },

    fetchPackages(): Promise<AxiosResponse<{ success: boolean; data: BillingPackage[] }>> {
        return apiClient.get('/member/billing/packages');
    },

    orderPackage(payload: OrderSubscriptionPayload): Promise<AxiosResponse<{ success: boolean; data: OrderSubscriptionResult }>> {
        return apiClient.post('/member/billing/order', payload);
    },

    checkoutTransaction(
        transactionId: string,
        billingPeriod: 'monthly' | 'yearly' = 'monthly',
    ): Promise<AxiosResponse<{ success: boolean; data: Record<string, unknown> }>> {
        return apiClient.post(`/member/billing/transactions/${transactionId}/checkout`, {
            billing_period: billingPeriod,
        });
    },
};

export default billingService;
