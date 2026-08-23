import * as SharedStyles from '@/shared/utils/styleUtils'
import { generateUUID as sharedGenerateUUID } from '@/shared/utils/uuid'

/**
 * Content Renderer Utilities - Proxying to Shared Logic
 * These are kept for backward compatibility and as a local entry point.
 */

export const getVal = SharedStyles.getVal
export const toCSS = SharedStyles.toCSS
export const getBorderStyles = SharedStyles.getBorderStyles
export const getSpacingStyles = SharedStyles.getSpacingStyles
export const getBoxShadowStyles = SharedStyles.getBoxShadowStyles
export const getBackgroundStyles = SharedStyles.getBackgroundStyles
export const getSizingStyles = SharedStyles.getSizingStyles
export const getTransformStyles = SharedStyles.getTransformStyles
export const getLayoutStyles = SharedStyles.getLayoutStyles

/**
 * Local helper that was originally only in renderer, now unified.
 */
export const generateUUID = sharedGenerateUUID

/**
 * Legacy resolve function for individual sensitive properties.
 */
export const resolve = (val: unknown, device: string = 'desktop') =>
    SharedStyles.getResponsiveValue({ key: val }, 'key', device)
