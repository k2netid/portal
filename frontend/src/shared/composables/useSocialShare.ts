import { logger } from '@/shared/utils/logger';

export function useSocialShare() {
    /**
     * Open popup window for sharing
     */
    const openPopup = (url: string, width = 600, height = 400) => {
        const left = (window.innerWidth - width) / 2;
        const top = (window.innerHeight - height) / 2;
        const options = `width=${width},height=${height},left=${left},top=${top},menubar=no,toolbar=no,resizable=yes,scrollbars=yes`;

        window.open(url, '_blank', options);
    };

    /**
     * Share on Facebook
     */
    const shareFacebook = (url: string, _title: string = '') => {
        const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
        openPopup(shareUrl);
    };

    /**
     * Share on Twitter/X
     */
    const shareTwitter = (url: string, text: string = '', hashtags: string[] = []) => {
        let shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}`;

        if (text) {
            shareUrl += `&text=${encodeURIComponent(text)}`;
        }

        if (hashtags.length > 0) {
            shareUrl += `&hashtags=${hashtags.join(',')}`;
        }

        openPopup(shareUrl);
    };

    /**
     * Share on LinkedIn
     */
    const shareLinkedIn = (url: string, _title: string = '', _summary: string = '') => {
        const shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
        openPopup(shareUrl);
    };

    /**
     * Share on WhatsApp
     */
    const shareWhatsApp = (url: string, text: string = '') => {
        const message = text ? `${text} ${url}` : url;
        const shareUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;

        // Check if mobile
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        if (isMobile) {
            // On mobile, open directly
            window.location.href = shareUrl;
        } else {
            // On desktop, open in new window
            openPopup(shareUrl, 600, 600);
        }
    };

    /**
     * Share on Telegram
     */
    const shareTelegram = (url: string, text: string = '') => {
        const shareUrl = `https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`;
        openPopup(shareUrl);
    };

    /**
     * Share via Email
     */
    const shareEmail = (url: string, subject: string = '', body: string = '') => {
        const mailBody = body ? `${body}\n\n${url}` : url;
        const mailtoUrl = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(mailBody)}`;
        window.location.href = mailtoUrl;
    };

    /**
     * Copy to clipboard
     */
    const copyToClipboard = async (text: string): Promise<boolean> => {
        try {
            // Modern async clipboard API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(text);
                return true;
            }

            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            
            if (!document.body) return false;
            
            try {
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                const successful = document.execCommand('copy');
                
                if (textArea.parentNode) {
                    document.body.removeChild(textArea);
                }
                return successful;
            } catch (err) {
                logger.error('Fallback clipboard error:', err);
                if (textArea.parentNode) {
                    try {
                        document.body.removeChild(textArea);
                    } catch (removeErr) {
                        logger.error('Failed to remove textarea:', removeErr);
                    }
                }
                return false;
            }
        } catch (err) {
            logger.error('Failed to copy to clipboard:', err);
            return false;
        }
    };

    /**
     * Native Web Share API (for mobile devices)
     */
    const nativeShare = async (data: ShareData): Promise<boolean> => {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: data.title || '',
                    text: data.text || '',
                    url: data.url || window.location.href,
                });
                return true;
            } catch (err) {
                // User cancelled or error occurred
                logger.error('Native share failed:', err);
                return false;
            }
        }
        return false;
    };

    /**
     * Check if native share is available
     */
    const canNativeShare = (): boolean => {
        return 'share' in navigator;
    };

    /**
     * Get share count (requires backend API)
     */
    const getShareCount = async (url: string): Promise<number> => {
        // This would typically call your backend API
        // which aggregates share counts from various platforms
        try {
            const response = await fetch(`/api/v1/share-count?url=${encodeURIComponent(url)}`);
            if (response.ok) {
                const data = await response.json();
                return data.count || 0;
            }
        } catch (err) {
            logger.error('Failed to fetch share count:', err);
        }
        return 0;
    };

    /**
     * Track share event (analytics)
     */
    const trackShare = async (platform: string, url: string): Promise<void> => {
        // Send analytics event to backend
        try {
            await fetch('/api/v1/analytics/share', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    platform,
                    url,
                    timestamp: new Date().toISOString(),
                }),
            });
        } catch (err) {
            logger.error('Failed to track share:', err);
        }
    };

    return {
        shareFacebook,
        shareTwitter,
        shareLinkedIn,
        shareWhatsApp,
        shareTelegram,
        shareEmail,
        copyToClipboard,
        nativeShare,
        canNativeShare,
        getShareCount,
        trackShare,
        openPopup,
    };
}
