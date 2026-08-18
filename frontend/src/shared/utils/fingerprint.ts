/**
 * Simple Canvas Fingerprinting Utility
 * 
 * This generates a unique identifier based on how the browser renders canvas elements.
 * It's used as an additional signal for bot detection.
 */
export const getCanvasFingerprint = (): string => {
    try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        if (!ctx) return 'not-supported';

        canvas.width = 200;
        canvas.height = 50;

        // Draw some text with different fonts and styles
        ctx.textBaseline = 'top';
        ctx.font = "14px 'Arial'";
        ctx.textBaseline = 'alphabetic';
        ctx.fillStyle = '#f60';
        ctx.fillRect(125, 1, 62, 20);
        ctx.fillStyle = '#069';
        ctx.fillText('Jejakawan Security Shield, <canvas> 1.0', 2, 15);
        ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
        ctx.fillText('Jejakawan Security Shield, <canvas> 1.0', 4, 17);

        // Draw some shapes
        ctx.strokeStyle = '#000';
        ctx.beginPath();
        ctx.arc(100, 25, 20, 0, Math.PI * 2, true);
        ctx.stroke();

        const result = canvas.toDataURL();
        
        // Simple hash function for the data URL
        let hash = 0;
        for (let i = 0; i < result.length; i++) {
            const char = result.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32bit integer
        }
        
        return hash.toString(16);
    } catch {
        return 'error';
    }
};
