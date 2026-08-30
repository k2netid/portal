import * as basic from './basicBlocks'
import type { ZodObject, ZodRawShape } from 'zod'

export const blockSchemas = {
    'heading': basic.headingSchema,
    'button': basic.buttonSchema,
    'image': basic.imageSchema,
    'text': basic.textSchema,
    'richtext': basic.textSchema, // reuse text schema for now
} as const

export type BlockType = keyof typeof blockSchemas

export function getSchemaForBlock(type: string): ZodObject<ZodRawShape> | null {
    if (Object.prototype.hasOwnProperty.call(blockSchemas, type)) {
        return blockSchemas[type as BlockType]
    }
    return null
}
