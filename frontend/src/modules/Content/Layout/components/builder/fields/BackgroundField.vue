<template>
  <div class="background-field">
    <!-- Tab Navigation -->
    <div class="bg-tabs">
        <div 
            v-for="tab in tabs" 
            :key="tab.id"
            class="bg-tab-item"
            :class="{ 'active': activeTab === tab.id }"
            @click="activeTab = tab.id"
            :title="tab.label"
        >
            <component :is="tab.icon" :size="16" />
        </div>
    </div>

    <!-- Tab Content -->
    <div class="bg-tab-content">
<!-- Background Color -->
        <div v-if="activeTab === 'color'" class="bg-section">
            <div class="flex items-center justify-between mb-2">
                <div class="bg-header mb-0">{{ t('builder.fields.background.color.label') }}</div>
                <FieldActions 
                    :label="t('builder.fields.background.color.label')"
                    :show-info="true"
                    show-responsive
                    :show-reset="hasOverride('backgroundColor') || !!getResponsiveValue('backgroundColor')"
                    @toggle-info="showColorInfo = $event"
                    @responsive="openResponsiveModal(t('builder.fields.background.color.label'), 'backgroundColor', 'color')"
                    @reset="updateSetting('backgroundColor', '')"
                />
            </div>

            <!-- Inline Info Panel -->
            <transition name="fade-slide">
                <div v-if="showColorInfo && getInfoContent('backgroundColor')" class="field-info-panel mb-4">
                    {{ getInfoContent('backgroundColor') }}
                </div>
            </transition>
            
            <div 
              class="bg-preview-box color-preview-box" 
              :class="{ 
                'is-inherited': !getResponsiveValue('backgroundColor') && getResponsivePlaceholder('backgroundColor'),
                'is-empty': !getResponsiveValue('backgroundColor') && !getResponsivePlaceholder('backgroundColor')
              }"
              :style="colorPreviewStyle"
              @click="focusColorInput"
              @mouseenter="isColorHovered = true"
              @mouseleave="isColorHovered = false"
            >
                <div v-if="!getResponsiveValue('backgroundColor') && getResponsivePlaceholder('backgroundColor')" class="inherited-badge">{{ t('builder.fields.background.color.inherited') }}</div>
                <div v-if="isColorHovered && (getResponsiveValue('backgroundColor') || getResponsivePlaceholder('backgroundColor'))" class="preview-actions-overlay">
                    <div class="action-btn-group">
                        <div class="action-icon-btn" :title="t('builder.contextMenu.reset')" @click.stop="resetBackgroundColor">
                            <RotateCcw :size="14" />
                        </div>
                        <div class="action-icon-btn remove" :title="t('builder.contextMenu.delete')" @click.stop="clearBackgroundColor">
                            <Trash2 :size="14" />
                        </div>
                    </div>
                </div>
                <div v-if="!getResponsiveValue('backgroundColor') && !getResponsivePlaceholder('backgroundColor')" class="empty-preview">
                    <Plus :size="20" />
                    <span>{{ t('builder.fields.background.color.add') }}</span>
                </div>
            </div>

            <ColorField 
                :field="{ name: 'backgroundColor', type: 'color', label: t('builder.fields.background.color.label') }"
                :value="getResponsiveValue('backgroundColor')"
                :placeholder-value="getResponsivePlaceholder('backgroundColor')"
                :module="module"
                :hide-preview="true"
                @update:value="updateSetting('backgroundColor', $event)"
                ref="colorFieldRef"
            />
        </div>

        <!-- Responsive Modal -->
        <ResponsiveFieldModal 
            v-if="responsiveModal.show"
            :label="responsiveModal.label"
            :base-key="responsiveModal.baseKey"
            :type="responsiveModal.type"
            :options="responsiveModal.options"
            :sub-fields="responsiveModal.subFields"
            :module="module"
            :settings="moduleSettings"
            @close="responsiveModal.show = false"
            @update="handleResponsiveUpdate"
        />

        <!-- Background Gradient -->
        <div v-if="activeTab === 'gradient'" class="bg-section">
            <div class="flex items-center justify-between flex-wrap mb-4">
                <div class="bg-header mb-0">{{ t('builder.fields.background.gradient.label') }}</div>
                <FieldActions 
                    :label="t('builder.fields.background.gradient.label')"
                    show-info
                    show-responsive
                    :show-reset="hasOverride('backgroundGradient') || !!getResponsiveValue('backgroundGradient')"
                    :show-dynamic-data="true"
                    @toggle-info="showGradientInfo = !showGradientInfo"
                    @responsive="openResponsiveModal(t('builder.fields.background.gradient.label'), 'backgroundGradient', 'gradient')"
                    @reset="updateSetting('backgroundGradient', '')"
                />
            </div>
            <transition name="fade-slide">
                <div v-if="showGradientInfo && getInfoContent('backgroundGradient')" class="field-info-panel mb-4">
                    {{ getInfoContent('backgroundGradient') }}
                </div>
            </transition>
            
            <div 
              v-if="!getResponsiveValue('backgroundGradient')"
              class="bg-preview-box gradient-preview-box is-empty"
              :style="gradientPreviewStyle"
              @click="initDefaultGradient"
            >
                <div class="empty-preview">
                    <Plus :size="20" />
                    <span>{{ t('builder.fields.background.gradient.add') }}</span>
                </div>
            </div>

            <template v-else>
                <!-- Redundant sub-header removed as per user request -->
                <GradientField 
                    :field="{ name: 'backgroundGradient', type: 'gradient', label: t('builder.fields.background.gradient.label') }"
                    :value="getResponsiveValue('backgroundGradient')"
                    :placeholder-value="getResponsivePlaceholder('backgroundGradient')"
                    :module="module"
                    @update:value="updateSetting('backgroundGradient', $event)"
                    @responsive="openResponsiveModal($event.label, 'backgroundGradient.' + $event.key, $event.type, $event.options)"
                />

                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.gradient.placeAbove') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.gradient.placeAbove')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundGradientShowAboveImage')"
                            :show-context-menu="true"
                            :show-dynamic-data="false"
                            @toggle-info="showGradientAboveInfo = $event"
                            @reset="updateSetting('backgroundGradientShowAboveImage', '')"
                            @responsive="openResponsiveModal(t('builder.fields.background.gradient.placeAbove'), 'backgroundGradientShowAboveImage', 'toggle')"
                        />
                    </div>
                    <transition name="fade-slide">
                        <div v-if="showGradientAboveInfo && getInfoContent('backgroundGradientShowAboveImage')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundGradientShowAboveImage') }}
                        </div>
                    </transition>
                    <ToggleField 
                        :field="{ name: 'backgroundGradientShowAboveImage', type: 'boolean', label: t('builder.fields.background.gradient.placeAbove') }"
                        :value="getResolvedValue('backgroundGradientShowAboveImage') === true || getResolvedValue('backgroundGradientShowAboveImage') === 'true'"
                        :placeholder-value="getResponsivePlaceholder('backgroundGradientShowAboveImage') === true"
                        @update:value="updateSetting('backgroundGradientShowAboveImage', $event)"
                    />
                </div>
            </template>
        </div>

        <!-- Background Image -->
        <div v-if="activeTab === 'image'" class="bg-section">
            <div class="flex items-center justify-between mb-2">
                <div class="bg-header mb-0">{{ t('builder.fields.background.image.label') }}</div>
                <FieldActions 
                    :label="t('builder.fields.background.image.label')"
                    :show-info="true"
                    show-responsive
                    :show-reset="hasOverride('backgroundImage') || !!getResponsiveValue('backgroundImage')"
                    @toggle-info="showImageInfo = $event"
                    @reset="updateSetting('backgroundImage', '')"
                    @responsive="openResponsiveModal(t('builder.fields.background.image.label'), 'backgroundImage', 'upload')"
                />
            </div>

            <!-- Inline Info Panel -->
            <transition name="fade-slide">
                <div v-if="showImageInfo && getInfoContent('backgroundImage')" class="field-info-panel mb-4">
                    {{ getInfoContent('backgroundImage') }}
                </div>
            </transition>
            
            <MediaPicker @selected="(media) => updateSetting('backgroundImage', media.url)" :constraints="{ allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'] }">
                <template #trigger="{ open }">
                    <div 
                      class="bg-preview-box image-preview-box cursor-pointer" 
                      :class="{ 
                        'is-inherited': !getResponsiveValue('backgroundImage') && getResponsivePlaceholder('backgroundImage'),
                        'is-empty': !getResponsiveValue('backgroundImage') && !getResponsivePlaceholder('backgroundImage')
                      }"
                      @click="open"
                      @mouseenter="isImageHovered = true"
                      @mouseleave="isImageHovered = false"
                    >
                        <div v-if="!getResponsiveValue('backgroundImage') && getResponsivePlaceholder('backgroundImage')" class="inherited-badge">{{ t('builder.fields.background.color.inherited') }}</div>
                        
                        <div v-if="getResolvedValue('backgroundImage')" class="preview-bg-image h-full w-full" :style="imagePreviewStyle"></div>

                        <div v-if="isImageHovered && (getResponsiveValue('backgroundImage') || getResponsivePlaceholder('backgroundImage'))" class="preview-actions-overlay">
                            <div class="action-btn-group">
                                <div class="action-icon-btn" :title="t('builder.fields.actions.edit')" @click.stop="open">
                                    <Settings :size="14" />
                                </div>
                                <div class="action-icon-btn remove" :title="t('builder.contextMenu.delete')" @click.stop="updateSetting('backgroundImage', '')">
                                    <Trash2 :size="14" />
                                </div>
                            </div>
                        </div>
                        <div v-if="!getResolvedValue('backgroundImage')" class="empty-preview">
                            <Plus :size="20" />
                            <span>{{ t('builder.fields.background.image.add') }}</span>
                        </div>
                    </div>
                </template>
            </MediaPicker>

            <UploadField 
                :field="{ name: 'backgroundImage', type: 'upload', label: t('builder.fields.background.image.label') }"
                :value="getResponsiveValue('backgroundImage')"
                :placeholder-value="getResponsivePlaceholder('backgroundImage')"
                :module="module"
                :allowed-extensions="['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']"
                :hide-preview="true"
                :hide-label="true"
                @update:value="updateSetting('backgroundImage', $event)"
            />

            <!-- Image Sub-settings (Only if image is set) -->
            <div v-if="getResolvedValue('backgroundImage')" class="image-sub-settings animate-in fade-in duration-300">
                <!-- Parallax Effect -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.image.parallax') }}</label>
                            <FieldActions 
                                :label="t('builder.fields.background.image.parallax')"
                                show-info
                                show-responsive
                                :show-reset="hasOverride('parallax')"
                                @toggle-info="showParallaxInfo = $event"
                                @reset="updateSetting('parallax', '')"
                                @responsive="openResponsiveModal(t('builder.fields.background.image.parallax'), 'parallax', 'toggle')"
                            />
                    </div>
                <!-- Inline Info Panel -->
                <transition name="fade-slide">
                    <div v-if="showParallaxInfo && getInfoContent('parallax')" class="field-info-panel mb-4">
                        {{ getInfoContent('parallax') }}
                    </div>
                </transition>
                <ToggleField 
                    :field="{ name: 'parallax', type: 'boolean', label: t('builder.fields.background.image.parallax') }"
                    :value="getResolvedValue('parallax') === true || getResolvedValue('parallax') === 'true'"
                    :placeholder-value="getResponsivePlaceholder('parallax') === true || getResponsivePlaceholder('parallax') === 'true'"
                    @update:value="updateSetting('parallax', $event)"
                />
            </div>

            <!-- Parallax Method (only visible if Parallax is TRUE) -->
            <div class="mt-4" v-if="getResolvedValue('parallax')">
                <div class="flex items-center justify-between mb-2">
                    <label class="builder-label mb-0">{{ t('builder.fields.background.image.parallaxMethod.label') }}</label>
                    <FieldActions 
                        :label="t('builder.fields.background.image.parallaxMethod.label')"
                        :show-info="true"
                        show-responsive
                        :show-reset="hasOverride('parallaxMethod') || !!getResponsiveValue('parallaxMethod')"
                        @toggle-info="showParallaxMethodInfo = $event"
                        @reset="updateSetting('parallaxMethod', '')"
                        @responsive="openResponsiveModal(t('builder.fields.background.image.parallaxMethod.label'), 'parallaxMethod', 'select', [
                            { label: t('builder.fields.background.image.parallaxMethod.options.true'), value: 'true' },
                            { label: t('builder.fields.background.image.parallaxMethod.options.css'), value: 'css' }
                        ])"
                    />
                </div>
                <!-- Inline Info Panel -->
                <transition name="fade-slide">
                    <div v-if="showParallaxMethodInfo && getInfoContent('parallaxMethod')" class="field-info-panel mb-4">
                        {{ getInfoContent('parallaxMethod') }}
                    </div>
                </transition>
                <SelectField 
                    :field="{ 
                        name: 'parallaxMethod', 
                        type: 'select',
                        label: t('builder.fields.background.image.parallaxMethod.label'),
                        options: [
                            { label: t('builder.fields.background.image.parallaxMethod.options.true'), value: 'true' },
                            { label: t('builder.fields.background.image.parallaxMethod.options.css'), value: 'css' }
                        ]
                    }"
                    :value="getResolvedValue('parallaxMethod') || 'true'" 
                    @update:value="updateSetting('parallaxMethod', $event)"
                    :hide-label="true"
                />
            </div>

            <!-- Standard Image Settings (Only if NOT parallax) -->
            <div v-if="!getResolvedValue('parallax')" class="image-standard-settings animate-in fade-in duration-300">
                <!-- Size -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.image.size.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.image.size.label')"
                            :show-info="true"
                            show-responsive
                            :show-reset="hasOverride('backgroundImageSize') || !!getResponsiveValue('backgroundImageSize')"
                            @toggle-info="showImageSizeInfo = $event"
                            @reset="updateSetting('backgroundImageSize', '')"
                            @responsive="openResponsiveModal(t('builder.fields.background.image.size.label'), 'backgroundImageSize', 'select', [
                                { label: t('builder.fields.background.image.size.options.cover'), value: 'cover' },
                                { label: t('builder.fields.background.image.size.options.fit'), value: 'fit' },
                                { label: t('builder.fields.background.image.size.options.auto'), value: 'auto' },
                                { label: t('builder.fields.background.image.size.options.stretch'), value: 'stretch' },
                                { label: t('builder.fields.background.image.size.options.custom'), value: 'custom' }
                            ], [
                                {
                                    match: 'custom',
                                    fields: [
                                        { name: 'backgroundImageWidth', label: t('builder.fields.common.width'), type: 'dimension' },
                                        { name: 'backgroundImageHeight', label: t('builder.fields.common.height'), type: 'dimension' }
                                    ]
                                }
                            ])"
                        />
                    </div>
                    <!-- Inline Info Panel -->
                    <transition name="fade-slide">
                        <div v-if="showImageSizeInfo && getInfoContent('backgroundImageSize')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundImageSize') }}
                        </div>
                    </transition>
                    <SelectField 
                        :field="{ 
                            name: 'backgroundImageSize', 
                            type: 'select',
                            label: t('builder.fields.background.image.size.label'),
                            options: [
                                { label: t('builder.fields.background.image.size.options.cover'), value: 'cover' },
                                { label: t('builder.fields.background.image.size.options.fit'), value: 'fit' },
                                { label: t('builder.fields.background.image.size.options.auto'), value: 'auto' },
                                { label: t('builder.fields.background.image.size.options.stretch'), value: 'stretch' },
                                { label: t('builder.fields.background.image.size.options.custom'), value: 'custom' }
                            ]
                        }"
                        :value="getResolvedValue('backgroundImageSize') || 'cover'" 
                        @update:value="updateSetting('backgroundImageSize', $event)"
                        :hide-label="true"
                    />
                </div>

                <div v-if="getResolvedValue('backgroundImageSize') === 'custom'" class="mt-4">
                    <div class="flex flex-col gap-8 relative">
                        <!-- Link Icon -->
                        <div 
                            class="absolute right-0 top-1/2 -translate-y-1/2 z-10 cursor-pointer p-1.5 rounded-full border border-input shadow-sm transition-colors duration-200"
                            :class="getResolvedValue('backgroundImageProportional') ? 'bg-blue-600 border-blue-600 text-white hover:bg-blue-700' : 'bg-background hover:text-accent border-input'"
                            @click="updateSetting('backgroundImageProportional', !getResolvedValue('backgroundImageProportional'))"
                            title="Proportional Scaling"
                        >
                            <component :is="getResolvedValue('backgroundImageProportional') ? Link : Unlink" :size="12" />
                        </div>

                        <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="builder-label mb-0">{{ t('builder.fields.common.width') }}</label>
                                    <FieldActions 
                                        :label="t('builder.fields.common.width')"
                                        show-info
                                        show-responsive
                                        :show-reset="hasOverride('backgroundImageWidth') || !!getResponsiveValue('backgroundImageWidth')"
                                        @toggle-info="showImageWidthInfo = $event"
                                        @reset="updateSetting('backgroundImageWidth', '')"
                                        @responsive="openResponsiveModal(t('builder.fields.common.width'), 'backgroundImageWidth', 'dimension')"
                                    />
                                </div>
                                <transition name="fade-slide">
                                    <div v-if="showImageWidthInfo && getInfoContent('backgroundImageWidth')" class="field-info-panel mb-4">
                                        {{ getInfoContent('backgroundImageWidth') }}
                                    </div>
                                </transition>
                                <DimensionField 
                                    :field="{ type: 'dimension' }"
                                    :value="getResolvedValue('backgroundImageWidth')"
                                    :placeholder-value="t('builder.fields.common.widthPlaceholder')"
                                    @update:value="updateSetting('backgroundImageWidth', $event)"
                                />
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="builder-label mb-0">{{ t('builder.fields.common.height') }}</label>
                                    <FieldActions 
                                        :label="t('builder.fields.common.height')"
                                        show-info
                                        show-responsive
                                        :show-reset="hasOverride('backgroundImageHeight') || !!getResponsiveValue('backgroundImageHeight')"
                                        @toggle-info="showImageHeightInfo = $event"
                                        @reset="updateSetting('backgroundImageHeight', '')"
                                        @responsive="openResponsiveModal(t('builder.fields.common.height'), 'backgroundImageHeight', 'dimension')"
                                    />
                                </div>
                                <transition name="fade-slide">
                                    <div v-if="showImageHeightInfo && getInfoContent('backgroundImageHeight')" class="field-info-panel mb-4">
                                        {{ getInfoContent('backgroundImageHeight') }}
                                    </div>
                                </transition>
                                <DimensionField 
                                    :field="{ type: 'dimension' }"
                                    :value="getResolvedValue('backgroundImageHeight')"
                                    :placeholder-value="t('builder.fields.common.heightPlaceholder')"
                                    @update:value="updateSetting('backgroundImageHeight', $event)"
                                />
                            </div>
                        </div>
                    </div>

                <!-- Position -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.image.position.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.image.position.label')"
                            :show-info="true"
                            show-responsive
                            :show-reset="hasOverride('backgroundImagePosition') || !!getResolvedValue('backgroundImagePosition')"
                            @toggle-info="showImagePositionInfo = $event"
                            @reset="updateSetting('backgroundImagePosition', '')"
                            @responsive="openResponsiveModal(t('builder.fields.background.image.position.label'), 'backgroundImagePosition', 'select', imagePositionOptions)"
                        />
                    </div>
                    <!-- Inline Info Panel -->
                    <transition name="fade-slide">
                        <div v-if="showImagePositionInfo && getInfoContent('backgroundImagePosition')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundImagePosition') }}
                        </div>
                    </transition>
                    <SelectField 
                        :field="{ 
                            name: 'backgroundImagePosition', 
                            type: 'select',
                            label: t('builder.fields.background.image.position.label'),
                            options: [
                                { label: t('builder.fields.background.image.position.options.topLeft'), value: 'left top' },
                                { label: t('builder.fields.background.image.position.options.topCenter'), value: 'center top' },
                                { label: t('builder.fields.background.image.position.options.topRight'), value: 'right top' },
                                { label: t('builder.fields.background.image.position.options.centerLeft'), value: 'left center' },
                                { label: t('builder.fields.background.image.position.options.center'), value: 'center center' },
                                { label: t('builder.fields.background.image.position.options.centerRight'), value: 'right center' },
                                { label: t('builder.fields.background.image.position.options.bottomLeft'), value: 'left bottom' },
                                { label: t('builder.fields.background.image.position.options.bottomCenter'), value: 'center bottom' },
                                { label: t('builder.fields.background.image.position.options.bottomRight'), value: 'right bottom' }
                            ]
                        }"
                        :value="getResolvedValue('backgroundImagePosition') || 'center'" 
                        @update:value="updateSetting('backgroundImagePosition', $event)"
                        :hide-label="true"
                    />
                </div>

                <!-- Repeat -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.image.repeat.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.image.repeat.label')"
                            :show-info="true"
                            show-responsive
                            :show-reset="hasOverride('backgroundImageRepeat') || !!getResolvedValue('backgroundImageRepeat')"
                            @toggle-info="showImageRepeatInfo = $event"
                            @reset="updateSetting('backgroundImageRepeat', '')"
                            @responsive="openResponsiveModal(t('builder.fields.background.image.repeat.label'), 'backgroundImageRepeat', 'select', [
                                { label: t('builder.fields.background.image.repeat.options.repeat'), value: 'repeat' },
                                { label: t('builder.fields.background.image.repeat.options.repeatX'), value: 'repeat-x' },
                                { label: t('builder.fields.background.image.repeat.options.repeatY'), value: 'repeat-y' },
                                { label: t('builder.fields.background.image.repeat.options.space'), value: 'space' },
                                { label: t('builder.fields.background.image.repeat.options.round'), value: 'round' },
                                { label: t('builder.fields.background.image.repeat.options.noRepeat'), value: 'no-repeat' }
                            ])"
                        />
                    </div>
                    <!-- Inline Info Panel -->
                    <transition name="fade-slide">
                        <div v-if="showImageRepeatInfo && getInfoContent('backgroundImageRepeat')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundImageRepeat') }}
                        </div>
                    </transition>
                    <SelectField 
                        :field="{ 
                            name: 'backgroundImageRepeat', 
                            type: 'select',
                            label: t('builder.fields.background.image.repeat.label'),
                            options: [
                                { label: t('builder.fields.background.image.repeat.options.repeat'), value: 'repeat' },
                                { label: t('builder.fields.background.image.repeat.options.repeatX'), value: 'repeat-x' },
                                { label: t('builder.fields.background.image.repeat.options.repeatY'), value: 'repeat-y' },
                                { label: t('builder.fields.background.image.repeat.options.space'), value: 'space' },
                                { label: t('builder.fields.background.image.repeat.options.round'), value: 'round' },
                                { label: t('builder.fields.background.image.repeat.options.noRepeat'), value: 'no-repeat' }
                            ]
                        }"
                        :value="getResolvedValue('backgroundImageRepeat') || 'no-repeat'" 
                        @update:value="updateSetting('backgroundImageRepeat', $event)"
                        :hide-label="true"
                    />
                </div>

                <!-- Attachment -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.image.attachment.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.image.attachment.label')"
                            :show-info="true"
                            show-responsive
                            :show-reset="hasOverride('backgroundImageAttachment') || !!getResolvedValue('backgroundImageAttachment')"
                            @toggle-info="showImageAttachmentInfo = $event"
                            @reset="updateSetting('backgroundImageAttachment', '')"
                            @responsive="openResponsiveModal(t('builder.fields.background.image.attachment.label'), 'backgroundImageAttachment', 'select', [
                                { label: t('builder.fields.background.image.attachment.options.scroll'), value: 'scroll' },
                                { label: t('builder.fields.background.image.attachment.options.fixed'), value: 'fixed' }
                            ])"
                        />
                    </div>
                    <!-- Inline Info Panel -->
                    <transition name="fade-slide">
                        <div v-if="showImageAttachmentInfo && getInfoContent('backgroundImageAttachment')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundImageAttachment') }}
                        </div>
                    </transition>
                    <SelectField 
                        :field="{ 
                            name: 'backgroundImageAttachment', 
                            type: 'select',
                            label: t('builder.fields.background.image.attachment.label'),
                            options: [
                                { label: t('builder.fields.background.image.attachment.options.scroll'), value: 'scroll' },
                                { label: t('builder.fields.background.image.attachment.options.fixed'), value: 'fixed' }
                            ]
                        }"
                        :value="getResolvedValue('backgroundImageAttachment') || 'scroll'" 
                        @update:value="updateSetting('backgroundImageAttachment', $event)"
                        :hide-label="true"
                    />
                </div>
            </div> <!-- End of standard image settings -->

             <!-- Blend Mode -->
            <div class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="builder-label mb-0">{{ t('builder.fields.background.image.blend') }}</label>
                    <FieldActions 
                        :label="t('builder.fields.background.image.blend')"
                        :show-info="true"
                        show-responsive
                        :show-reset="hasOverride('backgroundImageBlendMode') || !!getResponsiveValue('backgroundImageBlendMode')"
                        @toggle-info="showImageBlendInfo = $event"
                        @reset="updateSetting('backgroundImageBlendMode', '')"
                        @responsive="openResponsiveModal(t('builder.fields.background.image.blend'), 'backgroundImageBlendMode', 'select', [
                            { label: t('builder.settings.options.blend_mode.normal'), value: 'normal' },
                            { label: t('builder.settings.options.blend_mode.multiply'), value: 'multiply' },
                            { label: t('builder.settings.options.blend_mode.screen'), value: 'screen' },
                            { label: t('builder.settings.options.blend_mode.overlay'), value: 'overlay' },
                            { label: t('builder.settings.options.blend_mode.darken'), value: 'darken' },
                            { label: t('builder.settings.options.blend_mode.lighten'), value: 'lighten' },
                            { label: t('builder.settings.options.blend_mode.color-dodge'), value: 'color-dodge' },
                            { label: t('builder.settings.options.blend_mode.color-burn'), value: 'color-burn' },
                            { label: t('builder.settings.options.blend_mode.hard-light'), value: 'hard-light' },
                            { label: t('builder.settings.options.blend_mode.soft-light'), value: 'soft-light' },
                            { label: t('builder.settings.options.blend_mode.difference'), value: 'difference' },
                            { label: t('builder.settings.options.blend_mode.exclusion'), value: 'exclusion' },
                            { label: t('builder.settings.options.blend_mode.hue'), value: 'hue' },
                            { label: t('builder.settings.options.blend_mode.saturation'), value: 'saturation' },
                            { label: t('builder.settings.options.blend_mode.color'), value: 'color' },
                            { label: t('builder.settings.options.blend_mode.luminosity'), value: 'luminosity' }
                        ])"
                    />
                </div>
                <!-- Inline Info Panel -->
                <transition name="fade-slide">
                    <div v-if="showImageBlendInfo && getInfoContent('backgroundImageBlendMode')" class="field-info-panel mb-4">
                        {{ getInfoContent('backgroundImageBlendMode') }}
                    </div>
                </transition>
                <SelectField 
                    :field="{ 
                        name: 'backgroundImageBlendMode', 
                        type: 'select',
                        label: t('builder.fields.background.image.blend'),
                        options: [
                            { label: t('builder.settings.options.blend_mode.normal'), value: 'normal' },
                            { label: t('builder.settings.options.blend_mode.multiply'), value: 'multiply' },
                            { label: t('builder.settings.options.blend_mode.screen'), value: 'screen' },
                            { label: t('builder.settings.options.blend_mode.overlay'), value: 'overlay' },
                            { label: t('builder.settings.options.blend_mode.darken'), value: 'darken' },
                            { label: t('builder.settings.options.blend_mode.lighten'), value: 'lighten' },
                            { label: t('builder.settings.options.blend_mode.color-dodge'), value: 'color-dodge' },
                            { label: t('builder.settings.options.blend_mode.color-burn'), value: 'color-burn' },
                            { label: t('builder.settings.options.blend_mode.hard-light'), value: 'hard-light' },
                            { label: t('builder.settings.options.blend_mode.soft-light'), value: 'soft-light' },
                            { label: t('builder.settings.options.blend_mode.difference'), value: 'difference' },
                            { label: t('builder.settings.options.blend_mode.exclusion'), value: 'exclusion' },
                            { label: t('builder.settings.options.blend_mode.hue'), value: 'hue' },
                            { label: t('builder.settings.options.blend_mode.saturation'), value: 'saturation' },
                            { label: t('builder.settings.options.blend_mode.color'), value: 'color' },
                            { label: t('builder.settings.options.blend_mode.luminosity'), value: 'luminosity' }
                        ]
                    }"
                    :value="getResolvedValue('backgroundImageBlendMode') || 'normal'" 
                    @update:value="updateSetting('backgroundImageBlendMode', $event)"
                    :hide-label="true"
                />
            </div>
        </div>
    </div>

    <!-- Background Video -->
        <div v-if="activeTab === 'video'" class="bg-section">
             <div class="flex items-center justify-between mb-2">
                <div class="bg-header mb-0">{{ t('builder.fields.background.video.label') }}</div>
            </div>

            <!-- Inline Info Panel -->
            <transition name="fade-slide">
                <div v-if="showVideoInfo && getInfoContent('backgroundVideo')" class="field-info-panel mb-4">
                    {{ getInfoContent('backgroundVideo') }}
                </div>
            </transition>
             
            <div class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="builder-label mb-0">{{ t('builder.fields.background.video.mp4') }}</label>
                    <FieldActions 
                        :label="t('builder.fields.background.video.mp4')"
                        show-info
                        show-responsive
                        :show-reset="hasOverride('backgroundVideoMp4')"
                        @toggle-info="showVideoMp4Info = $event"
                        @reset="updateSetting('backgroundVideoMp4', '')"
                        @responsive="openResponsiveModal(t('builder.fields.background.video.mp4'), 'backgroundVideoMp4', 'upload', { allowedExtensions: ['mp4', 'mov', 'm4v'] })"
                    />
                </div>
                <transition name="fade-slide">
                    <div v-if="showVideoMp4Info && getInfoContent('backgroundVideoMp4')" class="field-info-panel mb-4">
                        {{ getInfoContent('backgroundVideoMp4') }}
                    </div>
                </transition>
                <MediaPicker @selected="(media) => updateSetting('backgroundVideoMp4', media.url)" :constraints="{ allowedExtensions: ['mp4', 'mov', 'm4v'] }">
                    <template #trigger="{ open }">
                        <div 
                        class="bg-preview-box video-preview-box cursor-pointer" 
                        :class="{ 
                            'is-inherited': !getResponsiveValue('backgroundVideoMp4') && getResponsivePlaceholder('backgroundVideoMp4'),
                            'is-empty': !getResponsiveValue('backgroundVideoMp4') && !getResponsivePlaceholder('backgroundVideoMp4')
                        }"
                        @click="open"
                        @mouseenter="isVideoHovered = true"
                        @mouseleave="isVideoHovered = false"
                        >
                            <video v-if="getResolvedValue('backgroundVideoMp4')" :src="getResolvedValue('backgroundVideoMp4')" class="preview-bg-video" :style="videoPreviewStyle" muted playsinline></video>

                            <div v-if="isVideoHovered && (getResponsiveValue('backgroundVideoMp4') || getResponsivePlaceholder('backgroundVideoMp4'))" class="preview-actions-overlay">
                                <div class="action-btn-group">
                                    <div class="action-icon-btn" :title="t('builder.fields.actions.edit')" @click.stop="open">
                                        <Settings :size="14" />
                                    </div>
                                    <div class="action-icon-btn remove" :title="t('builder.contextMenu.delete')" @click.stop="updateSetting('backgroundVideoMp4', '')">
                                        <Trash2 :size="14" />
                                    </div>
                                </div>
                            </div>
                            <div v-if="!getResolvedValue('backgroundVideoMp4')" class="empty-preview">
                                <Plus :size="20" />
                                <span>{{ t('builder.fields.background.video.add') }}</span>
                            </div>
                        </div>
                    </template>
                </MediaPicker>

                <UploadField 
                    :field="{ name: 'backgroundVideoMp4', type: 'upload', label: t('builder.fields.background.video.mp4') }"
                    :value="getResponsiveValue('backgroundVideoMp4')" 
                    @update:value="updateSetting('backgroundVideoMp4', $event)"
                    :allowed-extensions="['mp4', 'mov', 'm4v']"
                    :hide-label="true"
                    :hide-preview="true"
                />
            </div>
            
            <div class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="builder-label mb-0">{{ t('builder.fields.background.video.webm') }}</label>
                    <FieldActions 
                        :label="t('builder.fields.background.video.webm')"
                        show-info
                        show-responsive
                        :show-reset="hasOverride('backgroundVideoWebm')"
                        @toggle-info="showVideoWebmInfo = $event"
                        @reset="updateSetting('backgroundVideoWebm', '')"
                        @responsive="openResponsiveModal(t('builder.fields.background.video.webm'), 'backgroundVideoWebm', 'upload', { allowedExtensions: ['webm'] })"
                    />
                </div>
                <transition name="fade-slide">
                    <div v-if="showVideoWebmInfo && getInfoContent('backgroundVideoWebm')" class="field-info-panel mb-4">
                        {{ getInfoContent('backgroundVideoWebm') }}
                    </div>
                </transition>
                <MediaPicker @selected="(media) => updateSetting('backgroundVideoWebm', media.url)" :constraints="{ allowedExtensions: ['webm'] }">
                    <template #trigger="{ open }">
                        <div 
                        class="bg-preview-box video-preview-box cursor-pointer" 
                        :class="{ 
                            'is-inherited': !getResponsiveValue('backgroundVideoWebm') && getResponsivePlaceholder('backgroundVideoWebm'),
                            'is-empty': !getResponsiveValue('backgroundVideoWebm') && !getResponsivePlaceholder('backgroundVideoWebm')
                        }"
                        @click="open"
                        >
                            <video v-if="getResolvedValue('backgroundVideoWebm')" :src="getResolvedValue('backgroundVideoWebm')" class="preview-bg-video" :style="videoPreviewStyle" muted playsinline></video>

                            <div v-if="!getResolvedValue('backgroundVideoWebm')" class="empty-preview">
                                <Plus :size="20" />
                                <span>{{ t('builder.fields.background.video.add') }}</span>
                            </div>
                        </div>
                    </template>
                </MediaPicker>

                <UploadField 
                    :field="{ name: 'backgroundVideoWebm', type: 'upload', label: t('builder.fields.background.video.webm') }"
                    :value="getResponsiveValue('backgroundVideoWebm')" 
                    @update:value="updateSetting('backgroundVideoWebm', $event)"
                    :allowed-extensions="['webm']"
                    :hide-label="true"
                    :hide-preview="true"
                />
            </div>

            <!-- Video Sub-settings (Only if at least one video is set) -->
            <div v-if="getResolvedValue('backgroundVideoMp4') || getResolvedValue('backgroundVideoWebm')" class="video-sub-settings animate-in fade-in duration-300">
            <div class="mt-4 flex flex-col gap-8 relative">
                <!-- Link Icon -->
                <div 
                    class="absolute right-0 top-1/2 -translate-y-1/2 z-10 cursor-pointer p-1.5 rounded-full border border-input shadow-sm transition-colors duration-200"
                    :class="getResolvedValue('backgroundVideoProportional') ? 'bg-blue-600 border-blue-600 text-white hover:bg-blue-700' : 'bg-background hover:text-accent border-input'"
                    @click="updateSetting('backgroundVideoProportional', !getResolvedValue('backgroundVideoProportional'))"
                    title="Proportional Scaling"
                >
                    <component :is="getResolvedValue('backgroundVideoProportional') ? Link : Unlink" :size="12" />
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.video.width') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.video.width')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundVideoWidth') || !!getResponsiveValue('backgroundVideoWidth')"
                            @toggle-info="showVideoWidthInfo = $event"
                            @reset="updateSetting('backgroundVideoWidth', '')"
                            @responsive="openResponsiveModal(t('builder.fields.background.video.width'), 'backgroundVideoWidth', 'dimension')"
                        />
                    </div>
                    <transition name="fade-slide">
                        <div v-if="showVideoWidthInfo && getInfoContent('backgroundVideoWidth')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundVideoWidth') }}
                        </div>
                    </transition>
                    <DimensionField 
                        :field="{ type: 'dimension' }"
                        :value="getResolvedValue('backgroundVideoWidth')"
                        :placeholder-value="'100%'"
                        @update:value="updateSetting('backgroundVideoWidth', $event)"
                    />
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.video.height') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.video.height')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundVideoHeight') || !!getResponsiveValue('backgroundVideoHeight')"
                            @toggle-info="showVideoHeightInfo = $event"
                            @reset="updateSetting('backgroundVideoHeight', '')"
                            @responsive="openResponsiveModal(t('builder.fields.background.video.height'), 'backgroundVideoHeight', 'dimension')"
                        />
                    </div>
                    <transition name="fade-slide">
                        <div v-if="showVideoHeightInfo && getInfoContent('backgroundVideoHeight')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundVideoHeight') }}
                        </div>
                    </transition>
                    <DimensionField 
                        :field="{ type: 'dimension' }"
                        :value="getResolvedValue('backgroundVideoHeight')"
                        @update:value="updateSetting('backgroundVideoHeight', $event)"
                    />
                </div>
            </div>

            <div class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="builder-label mb-0">{{ t('builder.fields.background.video.pauseOnPlay') }}</label>
                    <FieldActions 
                        :label="t('builder.fields.background.video.pauseOnPlay')"
                        show-info
                        show-responsive
                        :show-reset="hasOverride('pauseVideoOnPlay')"
                        @toggle-info="showVideoPauseOnPlayInfo = $event"
                        @reset="updateSetting('pauseVideoOnPlay', '')"
                        @responsive="openResponsiveModal(t('builder.fields.background.video.pauseOnPlay'), 'pauseVideoOnPlay', 'toggle')"
                    />
                </div>
                <transition name="fade-slide">
                    <div v-if="showVideoPauseOnPlayInfo && getInfoContent('pauseVideoOnPlay')" class="field-info-panel mb-4">
                        {{ getInfoContent('pauseVideoOnPlay') }}
                    </div>
                </transition>
                <ToggleField 
                    :field="{ name: 'pauseVideoOnPlay', type: 'boolean', label: t('builder.fields.background.video.pauseOnPlay') }"
                    :value="getResolvedValue('pauseVideoOnPlay') === true" 
                    @update:value="updateSetting('pauseVideoOnPlay', $event)"
                />
            </div>
            
            <div class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="builder-label mb-0">{{ t('builder.fields.background.video.pauseNotInView') }}</label>
                    <FieldActions 
                        :label="t('builder.fields.background.video.pauseNotInView')"
                        show-info
                        show-responsive
                        :show-reset="hasOverride('pauseVideoNotInView')"
                        @toggle-info="showVideoPauseNotInViewInfo = $event"
                        @reset="updateSetting('pauseVideoNotInView', '')"
                        @responsive="openResponsiveModal(t('builder.fields.background.video.pauseNotInView'), 'pauseVideoNotInView', 'toggle')"
                    />
                </div>
                <transition name="fade-slide">
                    <div v-if="showVideoPauseNotInViewInfo && getInfoContent('pauseVideoNotInView')" class="field-info-panel mb-4">
                        {{ getInfoContent('pauseVideoNotInView') }}
                    </div>
                </transition>
                <ToggleField 
                    :field="{ name: 'pauseVideoNotInView', type: 'boolean', label: t('builder.fields.background.video.pauseNotInView') }"
                    :value="getResolvedValue('pauseVideoNotInView') === true" 
                    @update:value="updateSetting('pauseVideoNotInView', $event)"
                />
            </div>

            <!-- Loop -->
            <div class="mt-4">
                 <div class="flex items-center justify-between mb-2">
                    <label class="builder-label mb-0">{{ t('builder.fields.background.video.loop') }}</label>
                <FieldActions 
                    :label="t('builder.fields.background.video.loop')"
                    show-info
                    show-responsive
                    :show-reset="hasOverride('backgroundVideoLoop')"
                    @toggle-info="showVideoLoopInfo = $event"
                    @reset="updateSetting('backgroundVideoLoop', true)"
                    @responsive="openResponsiveModal(t('builder.fields.background.video.loop'), 'backgroundVideoLoop', 'toggle')"
                />
            </div>
            <transition name="fade-slide">
                <div v-if="showVideoLoopInfo && getInfoContent('backgroundVideoLoop')" class="field-info-panel mb-4">
                    {{ getInfoContent('backgroundVideoLoop') }}
                </div>
            </transition>
                <ToggleField 
                    :field="{ name: 'backgroundVideoLoop', type: 'boolean', label: t('builder.fields.background.video.loop') }"
                    :value="getResolvedValue('backgroundVideoLoop') !== false" 
                    @update:value="updateSetting('backgroundVideoLoop', $event)"
                />
            </div>

            <!-- Mute -->
            <div class="mt-4">
                 <div class="flex items-center justify-between mb-2">
                    <label class="builder-label mb-0">{{ t('builder.fields.background.video.mute') }}</label>
                <FieldActions 
                    :label="t('builder.fields.background.video.mute')"
                    show-info
                    show-responsive
                    :show-reset="hasOverride('backgroundVideoMute')"
                    @toggle-info="showVideoMuteInfo = $event"
                    @reset="updateSetting('backgroundVideoMute', true)"
                    @responsive="openResponsiveModal(t('builder.fields.background.video.mute'), 'backgroundVideoMute', 'toggle')"
                />
            </div>
            <transition name="fade-slide">
                <div v-if="showVideoMuteInfo && getInfoContent('backgroundVideoMute')" class="field-info-panel mb-4">
                    {{ getInfoContent('backgroundVideoMute') }}
                </div>
            </transition>
                <ToggleField 
                    :field="{ name: 'backgroundVideoMute', type: 'boolean', label: t('builder.fields.background.video.mute') }"
                    :value="getResolvedValue('backgroundVideoMute') !== false" 
                    @update:value="updateSetting('backgroundVideoMute', $event)"
                />
            </div>

            <!-- Video Overlay -->
            <div class="mt-4 pt-4 border-t border-input">
                 <div class="flex items-center justify-between mb-2">
                    <label class="builder-label mb-0">{{ t('builder.fields.background.video.overlay') || 'Video Overlay' }}</label>
                <FieldActions 
                    :label="t('builder.fields.background.video.overlay') || 'Video Overlay'"
                    show-info
                    show-responsive
                    :show-reset="hasOverride('backgroundVideoOverlayColor')"
                    @toggle-info="showVideoOverlayInfo = $event"
                    @reset="updateSetting('backgroundVideoOverlayColor', '')"
                    @responsive="openResponsiveModal(t('builder.fields.background.video.overlay') || 'Video Overlay', 'backgroundVideoOverlayColor', 'color')"
                />
            </div>
            <transition name="fade-slide">
                <div v-if="showVideoOverlayInfo && getInfoContent('backgroundVideoOverlayColor')" class="field-info-panel mb-4">
                    {{ getInfoContent('backgroundVideoOverlayColor') }}
                </div>
            </transition>
                <ColorField 
                    :value="getResolvedValue('backgroundVideoOverlayColor')" 
                    @update:value="updateSetting('backgroundVideoOverlayColor', $event)"
                />
            </div>
</div> <!-- End of video-sub-settings -->
        </div>

        <!-- Pattern -->
        <div v-if="activeTab === 'pattern'" class="bg-section">
            <div class="flex items-center justify-between mb-2">
                <div class="bg-header mb-0">{{ t('builder.fields.background.tabs.pattern') }}</div>
                <FieldActions 
                    :label="t('builder.fields.background.tabs.pattern')"
                    show-info
                    show-responsive
                    :show-reset="hasOverride('backgroundPattern')"
                    @toggle-info="showPatternInfo = $event"
                    @reset="updateSetting('backgroundPattern', 'none')"
                    @responsive="openResponsiveModal(t('builder.fields.background.tabs.pattern'), 'backgroundPattern', 'pattern', patternOptions, [
                        { fields: [
                            { name: 'backgroundPatternColor', label: t('builder.fields.background.pattern.color'), type: 'color' },
                            { name: 'backgroundPatternFlipH', label: t('builder.fields.background.pattern.flipH'), type: 'toggle' },
                            { name: 'backgroundPatternFlipV', label: t('builder.fields.background.pattern.flipV'), type: 'toggle' },
                            { name: 'backgroundPatternRotate', label: t('builder.fields.background.pattern.rotate'), type: 'toggle' },
                            { name: 'backgroundPatternInvert', label: t('builder.fields.background.pattern.invert'), type: 'toggle' },
                            { name: 'backgroundPatternSize', label: t('builder.fields.background.pattern.size.label'), type: 'select', options: patternSizeOptions },
                            { name: 'backgroundPatternWidth', label: t('builder.fields.background.pattern.size.width'), type: 'dimension' },
                            { name: 'backgroundPatternHeight', label: t('builder.fields.background.pattern.size.height'), type: 'dimension' },
                            { name: 'backgroundPatternRepeatOrigin', label: t('builder.fields.background.pattern.repeatOrigin.label'), type: 'select', options: patternRepeatOriginOptions },
                            { name: 'backgroundPatternHorizontalOffset', label: t('builder.fields.background.pattern.horizontalOffset'), type: 'dimension' },
                            { name: 'backgroundPatternVerticalOffset', label: t('builder.fields.background.pattern.verticalOffset'), type: 'dimension' },
                            { name: 'backgroundPatternRepeat', label: t('builder.fields.background.pattern.repeat.label'), type: 'select', options: patternRepeatOptions },
                            { name: 'backgroundPatternBlendMode', label: t('builder.fields.background.pattern.blendMode.label'), type: 'select', options: blendModeOptions }
                        ]}
                    ])"
                />
            </div>
            <transition name="fade-slide">
                <div v-if="showPatternInfo && getInfoContent('backgroundPattern')" class="field-info-panel mb-4">
                    {{ getInfoContent('backgroundPattern') }}
                </div>
            </transition>

            <!-- Empty State / Large Preview -->
            <div v-if="!getResolvedValue('backgroundPattern') || getResolvedValue('backgroundPattern') === 'none'" class="bg-preview-box is-empty" @click="initDefaultPattern">
                <div class="empty-preview">
                    <Plus :size="20" />
                    <span>{{ t('builder.fields.background.pattern.add') }}</span>
                </div>
            </div>

            <div v-else class="pattern-ui-container">
                <div 
                    class="bg-preview-box pattern-preview-box"
                    @mouseenter="isPatternHovered = true"
                    @mouseleave="isPatternHovered = false"
                >
                    <div 
                        v-if="activePattern" 
                        class="preview-bg-image h-full w-full"
                        :style="combinedBackgroundStyle"
                    ></div>
                    <div v-if="isPatternHovered" class="preview-actions-overlay">
                        <div class="action-btn-group">
                            <div class="action-icon-btn remove" :title="t('builder.contextMenu.delete')" @click.stop="clearPattern">
                                <Trash2 :size="14" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pattern Selection Dropdown (Moved Below Preview) -->
            <div class="select-field-container mt-2 mb-4">
                <BaseDropdown align="left" width="100%">
                    <template #trigger="{ open }">
                        <div class="select-trigger" :class="{ 'is-open': open }">
                            <span class="selected-label">{{ activePattern ? activePattern.label : t('builder.common.none') }}</span>
                            <ChevronDown :size="14" class="select-arrow" />
                        </div>
                    </template>

                    <template #default="{ close }">
                        <div class="dropdown-preview-container">
                            <PatternField 
                                :value="getResolvedValue('backgroundPattern')"
                                @update:value="updateSetting('backgroundPattern', $event); close()"
                            />
                        </div>
                    </template>
                </BaseDropdown>
            </div>

            <!-- Pattern Sub-settings -->
            <div v-if="getResolvedValue('backgroundPattern') && getResolvedValue('backgroundPattern') !== 'none'" class="pattern-sub-settings mt-4 animate-in fade-in duration-300">
                <!-- Pattern Color -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.pattern.color') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.pattern.color')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundPatternColor')"
                            @toggle-info="showPatternColorInfo = $event"
                            @reset="updateSetting('backgroundPatternColor', '')"
                            @responsive="openResponsiveModal(t('builder.fields.background.pattern.color'), 'backgroundPatternColor', 'color')"
                        />
                    </div>
                    <transition name="fade-slide">
                        <div v-if="showPatternColorInfo && getInfoContent('backgroundPatternColor')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundPatternColor') }}
                        </div>
                    </transition>
                    <ColorField 
                        :value="getResolvedValue('backgroundPatternColor')"
                        :placeholder-value="'rgba(0,0,0,0.1)'"
                        @update:value="updateSetting('backgroundPatternColor', $event)"
                    />
                </div>

                <!-- Pattern Transform -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.pattern.transform') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.pattern.transform')"
                            show-info
                            show-responsive
                            @toggle-info="showPatternTransformInfo = $event"
                            @responsive="openResponsiveModal(t('builder.fields.background.pattern.transform'), 'backgroundPatternFlipH', 'toggle', null, [
                                { fields: [
                                    { name: 'backgroundPattern', label: t('builder.fields.background.pattern.transform'), type: 'transform' },
                                ]}
                            ])"
                        />
                    </div>
                    <transition name="fade-slide">
                        <div v-if="showPatternTransformInfo && getInfoContent('backgroundPatternTransform')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundPatternTransform') }}
                        </div>
                    </transition>
                    <div class="flex gap-2">
                        <IconButton 
                            :icon="FlipHorizontal"
                            :title="t('builder.fields.background.pattern.flipH')"
                            :active="!!getResolvedValue('backgroundPatternFlipH')"
                            @click="updateSetting('backgroundPatternFlipH', !getResolvedValue('backgroundPatternFlipH'))"
                        />
                        <IconButton 
                            :icon="FlipVertical"
                            :title="t('builder.fields.background.pattern.flipV')"
                            :active="!!getResolvedValue('backgroundPatternFlipV')"
                            @click="updateSetting('backgroundPatternFlipV', !getResolvedValue('backgroundPatternFlipV'))"
                        />
                        <IconButton 
                            :icon="RefreshCw"
                            :title="t('builder.fields.background.pattern.rotate')"
                            :active="!!getResolvedValue('backgroundPatternRotate')"
                            @click="updateSetting('backgroundPatternRotate', !getResolvedValue('backgroundPatternRotate'))"
                        />
                        <IconButton 
                            :icon="Contrast"
                            :title="t('builder.fields.background.pattern.invert')"
                            :active="!!getResolvedValue('backgroundPatternInvert')"
                            @click="updateSetting('backgroundPatternInvert', !getResolvedValue('backgroundPatternInvert'))"
                        />
                    </div>
                </div>

                <!-- Pattern Size -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.pattern.size.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.pattern.size.label')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundPatternSize')"
                            @toggle-info="showPatternSizeInfo = $event"
                            @reset="updateSetting('backgroundPatternSize', 'actual')"
                            @responsive="openResponsiveModal(t('builder.fields.background.pattern.size.label'), 'backgroundPatternSize', 'select', patternSizeOptions)"
                        />
                    </div>
                    <!-- Inline Info Panel -->
                    <transition name="fade-slide">
                        <div v-if="showPatternSizeInfo && getInfoContent('backgroundPatternSize')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundPatternSize') }}
                        </div>
                    </transition>
                    <SelectField 
                        :field="{ name: 'backgroundPatternSize', type: 'select', options: patternSizeOptions }"
                        :value="getResolvedValue('backgroundPatternSize')"
                        @update:value="updateSetting('backgroundPatternSize', $event)"
                        :placeholder-value="'actual'"
                    />
                </div>

                <!-- Custom Size Width/Height -->
                <div v-if="getResolvedValue('backgroundPatternSize') === 'custom'" class="mt-4">
                    <div class="flex flex-col gap-8 relative">
                        <!-- Link Icon -->
                        <div 
                            class="absolute right-0 top-[50%] -translate-y-[50%] z-10 cursor-pointer flex items-center justify-center p-1 rounded transition-colors"
                            :class="[
                                (getResolvedValue('backgroundPatternProportional') === true || getResolvedValue('backgroundPatternProportional') === 'true') 
                                    ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                    : 'text-gray-400 hover:text-white hover:bg-white/10'
                            ]"
                            :title="t('builder.fields.background.proportional')"
                            @click="updateSetting('backgroundPatternProportional', !(getResolvedValue('backgroundPatternProportional') === true || getResolvedValue('backgroundPatternProportional') === 'true'))"
                        >
                            <Link v-if="getResolvedValue('backgroundPatternProportional') === true || getResolvedValue('backgroundPatternProportional') === 'true'" :size="14" />
                            <Unlink v-else :size="14" />
                        </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="builder-label mb-0">{{ t('builder.fields.background.pattern.size.width') }}</label>
                                    <FieldActions 
                                        :label="t('builder.fields.background.pattern.size.width')"
                                        show-info
                                        show-responsive
                                        :show-reset="hasOverride('backgroundPatternWidth') || !!getResponsiveValue('backgroundPatternWidth')"
                                        @toggle-info="showPatternWidthInfo = $event"
                                        @reset="updateSetting('backgroundPatternWidth', '')"
                                        @responsive="openResponsiveModal(t('builder.fields.background.pattern.size.width'), 'backgroundPatternWidth', 'dimension')"
                                    />
                                </div>
                                <transition name="fade-slide">
                                    <div v-if="showPatternWidthInfo && getInfoContent('backgroundPatternWidth')" class="field-info-panel mb-4">
                                        {{ getInfoContent('backgroundPatternWidth') }}
                                    </div>
                                </transition>
                                <DimensionField 
                                    :field="{ type: 'dimension' }"
                                    :value="getResolvedValue('backgroundPatternWidth')"
                                    :placeholder-value="'auto'"
                                    @update:value="updateSetting('backgroundPatternWidth', $event)"
                                />
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="builder-label mb-0">{{ t('builder.fields.background.pattern.size.height') }}</label>
                                    <FieldActions 
                                        :label="t('builder.fields.background.pattern.size.height')"
                                        show-info
                                        show-responsive
                                        :show-reset="hasOverride('backgroundPatternHeight') || !!getResponsiveValue('backgroundPatternHeight')"
                                        @toggle-info="showPatternHeightInfo = $event"
                                        @reset="updateSetting('backgroundPatternHeight', '')"
                                        @responsive="openResponsiveModal(t('builder.fields.background.pattern.size.height'), 'backgroundPatternHeight', 'dimension')"
                                    />
                                </div>
                                <transition name="fade-slide">
                                    <div v-if="showPatternHeightInfo && getInfoContent('backgroundPatternHeight')" class="field-info-panel mb-4">
                                        {{ getInfoContent('backgroundPatternHeight') }}
                                    </div>
                                </transition>
                                <DimensionField 
                                    :field="{ type: 'dimension' }"
                                    :value="getResolvedValue('backgroundPatternHeight')"
                                    :placeholder-value="'auto'"
                                    @update:value="updateSetting('backgroundPatternHeight', $event)"
                                />
                            </div>
                    </div>
                </div>

                <!-- Pattern Repeat Origin -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.pattern.repeatOrigin.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.pattern.repeatOrigin.label')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundPatternRepeatOrigin')"
                            @toggle-info="showPatternRepeatOriginInfo = $event"
                            @reset="updateSetting('backgroundPatternRepeatOrigin', 'center')"
                            @responsive="openResponsiveModal(t('builder.fields.background.pattern.repeatOrigin.label'), 'backgroundPatternRepeatOrigin', 'select', patternRepeatOriginOptions)"
                        />
                    </div>
                    
                    <transition name="fade-slide">
                        <div v-if="showPatternRepeatOriginInfo" class="field-info-panel mb-2">
                            {{ getInfoContent('backgroundPatternRepeatOrigin') }}
                        </div>
                    </transition>

                    <SelectField 
                        :field="{ name: 'backgroundPatternRepeatOrigin', type: 'select', options: patternRepeatOriginOptions }"
                        :value="getResolvedValue('backgroundPatternRepeatOrigin')"
                        :placeholder-value="'center'"
                        @update:value="updateSetting('backgroundPatternRepeatOrigin', $event)"
                    />
                </div>

                <!-- Pattern Offsets (Horizontal & Vertical) -->
                <div v-if="getResolvedValue('backgroundPatternRepeatOrigin') && getResolvedValue('backgroundPatternRepeatOrigin') !== 'center' && getResolvedValue('backgroundPatternRepeatOrigin') !== 'center center'" class="mt-4 flex flex-col gap-4">
                    <!-- Horizontal Offset -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="builder-label mb-0">{{ t('builder.fields.background.pattern.horizontalOffset') }}</label>
                            <FieldActions 
                                :label="t('builder.fields.background.pattern.horizontalOffset')"
                                show-info
                                show-responsive
                                :show-reset="hasOverride('backgroundPatternHorizontalOffset')"
                                @toggle-info="showPatternHorizontalOffsetInfo = $event"
                                @reset="updateSetting('backgroundPatternHorizontalOffset', '0%')"
                                @responsive="openResponsiveModal(t('builder.fields.background.pattern.horizontalOffset'), 'backgroundPatternHorizontalOffset', 'input')"
                            />
                        </div>
                        <transition name="fade-slide">
                            <div v-if="showPatternHorizontalOffsetInfo" class="field-info-panel mb-2">
                                {{ getInfoContent('backgroundPatternHorizontalOffset') }}
                            </div>
                        </transition>
                        <DimensionField 
                            :field="{ type: 'dimension' }"
                            :value="getResolvedValue('backgroundPatternHorizontalOffset')"
                            :placeholder-value="'0%'"
                            @update:value="updateSetting('backgroundPatternHorizontalOffset', $event)"
                        />
                    </div>
                    
                    <!-- Vertical Offset -->
                    <div>
                         <div class="flex items-center justify-between mb-2">
                            <label class="builder-label mb-0">{{ t('builder.fields.background.pattern.verticalOffset') }}</label>
                            <FieldActions 
                                :label="t('builder.fields.background.pattern.verticalOffset')"
                                show-info
                                show-responsive
                                :show-reset="hasOverride('backgroundPatternVerticalOffset')"
                                @toggle-info="showPatternVerticalOffsetInfo = $event"
                                @reset="updateSetting('backgroundPatternVerticalOffset', '0%')"
                                @responsive="openResponsiveModal(t('builder.fields.background.pattern.verticalOffset'), 'backgroundPatternVerticalOffset', 'input')"
                            />
                        </div>
                        <transition name="fade-slide">
                            <div v-if="showPatternVerticalOffsetInfo" class="field-info-panel mb-2">
                                {{ getInfoContent('backgroundPatternVerticalOffset') }}
                            </div>
                        </transition>
                        <DimensionField 
                            :field="{ type: 'dimension' }"
                            :value="getResolvedValue('backgroundPatternVerticalOffset')"
                            :placeholder-value="'0%'"
                            @update:value="updateSetting('backgroundPatternVerticalOffset', $event)"
                        />
                    </div>
                </div>

                <!-- Pattern Repeat -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.pattern.repeat.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.pattern.repeat.label')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundPatternRepeat')"
                            @toggle-info="showPatternRepeatInfo = $event"
                            @reset="updateSetting('backgroundPatternRepeat', 'repeat')"
                            @responsive="openResponsiveModal(t('builder.fields.background.pattern.repeat.label'), 'backgroundPatternRepeat', 'select', patternRepeatOptions)"
                        />
                    </div>
                    <!-- Inline Info Panel -->
                    <transition name="fade-slide">
                        <div v-if="showPatternRepeatInfo && getInfoContent('backgroundPatternRepeat')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundPatternRepeat') }}
                        </div>
                    </transition>
                    <SelectField 
                        :field="{ name: 'backgroundPatternRepeat', type: 'select', options: patternRepeatOptions }"
                        :value="getResolvedValue('backgroundPatternRepeat')"
                        @update:value="updateSetting('backgroundPatternRepeat', $event)"
                        :placeholder-value="'repeat'"
                    />
                </div>

                <!-- Pattern Blend Mode -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.pattern.blendMode.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.pattern.blendMode.label')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundPatternBlendMode')"
                            @toggle-info="showPatternBlendInfo = $event"
                            @reset="updateSetting('backgroundPatternBlendMode', 'normal')"
                            @responsive="openResponsiveModal(t('builder.fields.background.pattern.blendMode.label'), 'backgroundPatternBlendMode', 'select', blendModeOptions)"
                        />
                    </div>
                    <!-- Inline Info Panel -->
                    <transition name="fade-slide">
                        <div v-if="showPatternBlendInfo && getInfoContent('backgroundPatternBlendMode')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundPatternBlendMode') }}
                        </div>
                    </transition>
                    <SelectField 
                        :field="{ name: 'backgroundPatternBlendMode', type: 'select', options: blendModeOptions }"
                        :value="getResolvedValue('backgroundPatternBlendMode')"
                        @update:value="updateSetting('backgroundPatternBlendMode', $event)"
                        :placeholder-value="'normal'"
                    />
                </div>
            </div>
        </div>

        <!-- Mask -->
        <div v-if="activeTab === 'mask'" class="bg-section">
            <div class="flex items-center justify-between mb-2">
                <div class="bg-header mb-0">{{ t('builder.fields.background.tabs.mask') }}</div>
                <FieldActions 
                    :label="t('builder.fields.background.tabs.mask')"
                    show-info
                    show-responsive
                    :show-reset="hasOverride('backgroundMask')"
                    @toggle-info="showMaskInfo = $event"
                    @reset="updateSetting('backgroundMask', 'none')"
                    @responsive="openResponsiveModal(t('builder.fields.background.tabs.mask'), 'backgroundMask', 'mask', maskOptions, [
                        { fields: [
                            { name: 'backgroundMaskColor', label: t('builder.fields.background.mask.color'), type: 'color' },
                            { name: 'backgroundMask', label: t('builder.fields.background.mask.transform'), type: 'transform' },
                            { name: 'backgroundMaskSize', label: t('builder.fields.background.mask.size.label'), type: 'select', options: patternSizeOptions },
                            { name: 'backgroundMaskWidth', label: t('builder.fields.background.mask.size.width'), type: 'dimension' },
                            { name: 'backgroundMaskHeight', label: t('builder.fields.background.mask.size.height'), type: 'dimension' },
                            { name: 'backgroundMaskPosition', label: t('builder.fields.background.mask.position.label'), type: 'select', options: imagePositionOptions },
                            { name: 'backgroundMaskAspectRatio', label: t('builder.fields.background.mask.aspectRatio.label'), type: 'select', options: maskAspectRatioOptions },
                            { name: 'backgroundMaskBlendMode', label: t('builder.fields.background.mask.blend_mode.label'), type: 'select', options: blendModeOptions }
                        ]}
                    ])"
                />
            </div>
            <transition name="fade-slide">
                <div v-if="showMaskInfo && getInfoContent('backgroundMask')" class="field-info-panel mb-4">
                    {{ getInfoContent('backgroundMask') }}
                </div>
            </transition>

            <!-- Preview Box -->
            <div 
                class="bg-preview-box mb-4"
                :class="{ 'is-empty': !getResolvedValue('backgroundMask') || getResolvedValue('backgroundMask') === 'none' }"
                @mouseenter="isMaskHovered = true"
                @mouseleave="isMaskHovered = false"
            >
                <div 
                    v-if="getResolvedValue('backgroundMask') && getResolvedValue('backgroundMask') !== 'none'" 
                    class="relative w-full h-full"
                    :style="combinedBackgroundStyle"
                ></div>
                
                <div v-if="isMaskHovered && getResolvedValue('backgroundMask') && getResolvedValue('backgroundMask') !== 'none'" class="preview-actions-overlay">
                    <div class="action-btn-group">
                        <div class="action-icon-btn remove" :title="t('builder.contextMenu.delete')" @click.stop="updateSetting('backgroundMask', 'none')">
                            <Trash2 :size="14" />
                        </div>
                    </div>
                </div>
                
                <div v-if="(!getResolvedValue('backgroundMask') || getResolvedValue('backgroundMask') === 'none')" class="empty-preview cursor-pointer" @click="initDefaultMask">
                    <Plus :size="20" />
                    <span>{{ t('builder.fields.background.mask.add') }}</span>
                </div>
            </div>

            <!-- Mask Selection Dropdown with Previews -->
            <div class="select-field-container">
                <BaseDropdown align="left" width="100%">
                    <template #trigger="{ open }">
                        <div class="select-trigger" :class="{ 'is-open': open }">
                            <span class="selected-label">{{ activeMask ? t('builder.fields.background.mask.options.' + activeMask.id) : t('builder.common.none') }}</span>
                            <ChevronDown :size="14" class="select-arrow" />
                        </div>
                    </template>

                    <template #default="{ close }">
                        <div class="p-2 dropdown-preview-container">
                            <MaskField 
                                :value="getResolvedValue('backgroundMask')"
                                @update:value="updateSetting('backgroundMask', $event); close()"
                            />
                        </div>
                    </template>
                </BaseDropdown>
            </div>

            <!-- Mask Sub-settings -->
            <div v-if="getResolvedValue('backgroundMask') && getResolvedValue('backgroundMask') !== 'none'" class="mask-sub-settings mt-4 animate-in fade-in duration-300">
                <!-- Mask Color -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.mask.color') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.mask.color')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundMaskColor')"
                            @toggle-info="showMaskColorInfo = $event"
                            @reset="updateSetting('backgroundMaskColor', '')"
                            @responsive="openResponsiveModal(t('builder.fields.background.mask.color'), 'backgroundMaskColor', 'color')"
                        />
                    </div>
                    <transition name="fade-slide">
                        <div v-if="showMaskColorInfo && getInfoContent('backgroundMaskColor')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundMaskColor') }}
                        </div>
                    </transition>
                    <ColorField 
                        :value="getResolvedValue('backgroundMaskColor')"
                        :placeholder-value="'rgba(0,0,0,0.1)'"
                        @update:value="updateSetting('backgroundMaskColor', $event)"
                    />
                </div>

                <!-- Mask Transform -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.mask.transform') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.mask.transform')"
                            show-info
                            show-responsive
                            @toggle-info="showMaskTransformInfo = $event"
                            @responsive="openResponsiveModal(t('builder.fields.background.mask.transform'), 'backgroundMaskFlipH', 'toggle', null, [
                                { fields: [
                                    { name: 'backgroundMaskFlipH', label: t('builder.fields.background.mask.flipH'), type: 'toggle' },
                                    { name: 'backgroundMaskFlipV', label: t('builder.fields.background.mask.flipV'), type: 'toggle' },
                                    { name: 'backgroundMaskRotate', label: t('builder.fields.background.mask.rotate'), type: 'toggle' },
                                    { name: 'backgroundMaskInvert', label: t('builder.fields.background.mask.invert'), type: 'toggle' }
                                ]}
                            ])"
                        />
                    </div>
                    <transition name="fade-slide">
                        <div v-if="showMaskTransformInfo && getInfoContent('backgroundMaskTransform')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundMaskTransform') }}
                        </div>
                    </transition>
                    <div class="flex gap-2">
                        <IconButton 
                            :icon="FlipHorizontal"
                            :title="t('builder.fields.background.mask.flipH')"
                            :active="!!getResolvedValue('backgroundMaskFlipH')"
                            @click="updateSetting('backgroundMaskFlipH', !getResolvedValue('backgroundMaskFlipH'))"
                        />
                        <IconButton 
                            :icon="FlipVertical"
                            :title="t('builder.fields.background.mask.flipV')"
                            :active="!!getResolvedValue('backgroundMaskFlipV')"
                            @click="updateSetting('backgroundMaskFlipV', !getResolvedValue('backgroundMaskFlipV'))"
                        />
                        <IconButton 
                            :icon="RefreshCw"
                            :title="t('builder.fields.background.mask.rotate')"
                            :active="!!getResolvedValue('backgroundMaskRotate')"
                            @click="updateSetting('backgroundMaskRotate', !getResolvedValue('backgroundMaskRotate'))"
                        />
                        <IconButton 
                            :icon="Contrast"
                            :title="t('builder.fields.background.mask.invert')"
                            :active="!!getResolvedValue('backgroundMaskInvert')"
                            @click="updateSetting('backgroundMaskInvert', !getResolvedValue('backgroundMaskInvert'))"
                        />
                    </div>
                </div>

                <!-- Mask Size -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.mask.size.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.mask.size.label')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundMaskSize')"
                            @toggle-info="showMaskSizeInfo = $event"
                            @reset="updateSetting('backgroundMaskSize', 'stretch')"
                            @responsive="openResponsiveModal(t('builder.fields.background.mask.size.label'), 'backgroundMaskSize', 'select', patternSizeOptions)"
                        />
                    </div>
                    <transition name="fade-slide">
                        <div v-if="showMaskSizeInfo && getInfoContent('backgroundMaskSize')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundMaskSize') }}
                        </div>
                    </transition>
                    <SelectField 
                        :field="{ name: 'backgroundMaskSize', type: 'select', options: patternSizeOptions }"
                        :value="getResolvedValue('backgroundMaskSize')"
                        @update:value="updateSetting('backgroundMaskSize', $event)"
                        :placeholder-value="'stretch'"
                    />
                </div>

                <!-- Custom Size Width/Height -->
                <div v-if="getResolvedValue('backgroundMaskSize') === 'custom'" class="mt-4">
                    <div class="flex flex-col gap-8 relative">
                        <!-- Link Icon -->
                        <div 
                            class="absolute right-0 top-[50%] -translate-y-[50%] z-10 cursor-pointer flex items-center justify-center p-1 rounded transition-colors"
                            :class="[
                                (getResolvedValue('backgroundMaskProportional') === true || getResolvedValue('backgroundMaskProportional') === 'true') 
                                    ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                    : 'text-gray-400 hover:text-white hover:bg-white/10'
                            ]"
                            :title="t('builder.fields.background.proportional')"
                            @click="updateSetting('backgroundMaskProportional', !(getResolvedValue('backgroundMaskProportional') === true || getResolvedValue('backgroundMaskProportional') === 'true'))"
                        >
                            <Link v-if="getResolvedValue('backgroundMaskProportional') === true || getResolvedValue('backgroundMaskProportional') === 'true'" :size="14" />
                            <Unlink v-else :size="14" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="builder-label mb-0">{{ t('builder.fields.background.mask.size.width') }}</label>
                                <FieldActions 
                                    :label="t('builder.fields.background.mask.size.width')"
                                    show-info
                                    show-responsive
                                    :show-reset="hasOverride('backgroundMaskWidth') || !!getResponsiveValue('backgroundMaskWidth')"
                                    @toggle-info="showMaskWidthInfo = $event"
                                    @reset="updateSetting('backgroundMaskWidth', '')"
                                    @responsive="openResponsiveModal(t('builder.fields.background.mask.size.width'), 'backgroundMaskWidth', 'dimension')"
                                />
                            </div>
                            <transition name="fade-slide">
                                <div v-if="showMaskWidthInfo && getInfoContent('backgroundMaskWidth')" class="field-info-panel mb-4">
                                    {{ getInfoContent('backgroundMaskWidth') }}
                                </div>
                            </transition>
                            <DimensionField 
                                :field="{ type: 'dimension' }"
                                :value="getResolvedValue('backgroundMaskWidth')"
                                :placeholder-value="'auto'"
                                @update:value="updateSetting('backgroundMaskWidth', $event)"
                            />
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="builder-label mb-0">{{ t('builder.fields.background.mask.size.height') }}</label>
                                <FieldActions 
                                    :label="t('builder.fields.background.mask.size.height')"
                                    show-info
                                    show-responsive
                                    :show-reset="hasOverride('backgroundMaskHeight') || !!getResponsiveValue('backgroundMaskHeight')"
                                    @toggle-info="showMaskHeightInfo = $event"
                                    @reset="updateSetting('backgroundMaskHeight', '')"
                                    @responsive="openResponsiveModal(t('builder.fields.background.mask.size.height'), 'backgroundMaskHeight', 'dimension')"
                                />
                            </div>
                            <transition name="fade-slide">
                                <div v-if="showMaskHeightInfo && getInfoContent('backgroundMaskHeight')" class="field-info-panel mb-4">
                                    {{ getInfoContent('backgroundMaskHeight') }}
                                </div>
                            </transition>
                            <DimensionField 
                                :field="{ type: 'dimension' }"
                                :value="getResolvedValue('backgroundMaskHeight')"
                                :placeholder-value="'auto'"
                                @update:value="updateSetting('backgroundMaskHeight', $event)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Mask Position -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.mask.position.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.mask.position.label')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundMaskPosition')"
                            @toggle-info="showMaskPositionInfo = $event"
                            @reset="updateSetting('backgroundMaskPosition', 'center')"
                            @responsive="openResponsiveModal(t('builder.fields.background.mask.position.label'), 'backgroundMaskPosition', 'select', imagePositionOptions)"
                        />
                    </div>
                    <transition name="fade-slide">
                        <div v-if="showMaskPositionInfo && getInfoContent('backgroundMaskPosition')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundMaskPosition') }}
                        </div>
                    </transition>
                    <SelectField 
                        :field="{ name: 'backgroundMaskPosition', type: 'select', options: imagePositionOptions }"
                        :value="getResolvedValue('backgroundMaskPosition')"
                        @update:value="updateSetting('backgroundMaskPosition', $event)"
                        :placeholder-value="'center'"
                    />
                </div>

                <!-- Mask Aspect Ratio -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.mask.aspectRatio.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.mask.aspectRatio.label')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundMaskAspectRatio')"
                            @toggle-info="showMaskAspectRatioInfo = $event"
                            @reset="updateSetting('backgroundMaskAspectRatio', 'square')"
                            @responsive="openResponsiveModal(t('builder.fields.background.mask.aspectRatio.label'), 'backgroundMaskAspectRatio', 'select', maskAspectRatioOptions)"
                        />
                    </div>
                    <transition name="fade-slide">
                        <div v-if="showMaskAspectRatioInfo && getInfoContent('backgroundMaskAspectRatio')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundMaskAspectRatio') }}
                        </div>
                    </transition>
                    <div class="flex gap-2">
                        <IconButton 
                            v-for="opt in maskAspectRatioOptions" 
                            :key="opt.value"
                            :icon="opt.icon"
                            :title="opt.label"
                            :active="getResolvedValue('backgroundMaskAspectRatio') === opt.value"
                            @click="updateSetting('backgroundMaskAspectRatio', opt.value)"
                        />
                    </div>
                </div>

                <!-- Mask Blend Mode -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="builder-label mb-0">{{ t('builder.fields.background.mask.blend_mode.label') }}</label>
                        <FieldActions 
                            :label="t('builder.fields.background.mask.blend_mode.label')"
                            show-info
                            show-responsive
                            :show-reset="hasOverride('backgroundMaskBlendMode')"
                            @toggle-info="showMaskBlendInfo = $event"
                            @reset="updateSetting('backgroundMaskBlendMode', 'normal')"
                            @responsive="openResponsiveModal(t('builder.fields.background.mask.blend_mode.label'), 'backgroundMaskBlendMode', 'select', blendModeOptions)"
                        />
                    </div>
                    <transition name="fade-slide">
                        <div v-if="showMaskBlendInfo && getInfoContent('backgroundMaskBlendMode')" class="field-info-panel mb-4">
                            {{ getInfoContent('backgroundMaskBlendMode') }}
                        </div>
                    </transition>
                    <SelectField 
                        :field="{ name: 'backgroundMaskBlendMode', type: 'select', options: blendModeOptions }"
                        :value="getResolvedValue('backgroundMaskBlendMode')"
                        @update:value="updateSetting('backgroundMaskBlendMode', $event)"
                        :placeholder-value="'normal'"
                    />
                </div>
            </div>
        </div>
</div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, inject } from 'vue'
import type { BlockInstance, BuilderInstance, SettingDefinition, SubFieldGroup } from '@/types/builder'
import { useI18n } from 'vue-i18n'
import PaintBucket from 'lucide-vue-next/dist/esm/icons/paint-bucket.js';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import Video from 'lucide-vue-next/dist/esm/icons/video.js';
import Grid3X3 from 'lucide-vue-next/dist/esm/icons/grid-3x3.js';
import Moon from 'lucide-vue-next/dist/esm/icons/moon.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Component from 'lucide-vue-next/dist/esm/icons/component.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import FlipHorizontal from 'lucide-vue-next/dist/esm/icons/flip-horizontal.js';
import FlipVertical from 'lucide-vue-next/dist/esm/icons/flip-vertical.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Link from 'lucide-vue-next/dist/esm/icons/link.js';
import Unlink from 'lucide-vue-next/dist/esm/icons/unlink.js';
import Square from 'lucide-vue-next/dist/esm/icons/square.js';
import RectangleHorizontal from 'lucide-vue-next/dist/esm/icons/rectangle-horizontal.js';
import RectangleVertical from 'lucide-vue-next/dist/esm/icons/rectangle-vertical.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';

import Contrast from 'lucide-vue-next/dist/esm/icons/contrast.js';
import ColorField from '@/components/builder/fields/ColorField.vue'
import GradientField from '@/components/builder/fields/GradientField.vue'
import UploadField from '@/components/builder/fields/UploadField.vue'
import ToggleField from '@/components/builder/fields/ToggleField.vue'
import SelectField from '@/components/builder/fields/SelectField.vue'

import DimensionField from '@/components/builder/fields/DimensionField.vue'
import PatternField from '@/components/builder/fields/PatternField.vue'
import MaskField from '@/components/builder/fields/MaskField.vue'
import { IconButton } from '@/components/builder/ui'
import MediaPicker from '@/components/media/MediaPicker.vue'
import ResponsiveFieldModal from '@/components/builder/modals/ResponsiveFieldModal.vue'
import FieldActions from '@/components/builder/fields/FieldActions.vue'
import { BaseDropdown } from '@/components/builder/ui'
import { getHarmoniousGradientColors, getBackgroundStyles, generateGradientCSS } from '@/shared/utils/styleUtils'
import type { Gradient } from '@/shared/utils/styleUtils'
import { BackgroundPatterns, BackgroundMasks } from '@/shared/utils/AssetLibrary'

const props = withDefaults(defineProps<{
  field?: SettingDefinition
  module: BlockInstance
  device?: string
}>(), {
  field: undefined,
  device: undefined
})

const { t } = useI18n()
const builder = inject<BuilderInstance>('builder')

const activeTab = ref('color')
const colorFieldRef = ref<{ focus: () => void, openPicker: () => void } | null>(null)
const isColorHovered = ref(false)
const isImageHovered = ref(false)
const isVideoHovered = ref(false)
const isPatternHovered = ref(false)
const isMaskHovered = ref(false)

interface ResponsiveModalState {
    show: boolean;
    label: string;
    baseKey: string;
    type: string;
    options?: unknown[] | Record<string, unknown>;
    subFields?: SubFieldGroup[];
}

const responsiveModal = ref<ResponsiveModalState>({
    show: false,
    label: '',
    baseKey: '',
    type: 'color',
    options: undefined,
    subFields: []
})

const showColorInfo = ref(false)
const showGradientInfo = ref(false)
const showGradientAboveInfo = ref(false)
const showImageInfo = ref(false)
const showImageSizeInfo = ref(false)
const showImageWidthInfo = ref(false)
const showImageHeightInfo = ref(false)
const showImagePositionInfo = ref(false)
const showImageRepeatInfo = ref(false)
const showImageAttachmentInfo = ref(false)
const showImageBlendInfo = ref(false)
const showVideoInfo = ref(false)
const showVideoMp4Info = ref(false)
const showVideoWebmInfo = ref(false)
const showVideoWidthInfo = ref(false)
const showVideoHeightInfo = ref(false)
const showPatternWidthInfo = ref(false)
const showPatternHeightInfo = ref(false)
const showVideoPauseOnPlayInfo = ref(false)
const showVideoPauseNotInViewInfo = ref(false)

const showPatternInfo = ref(false)
const showPatternColorInfo = ref(false)
const showPatternTransformInfo = ref(false)
const showPatternSizeInfo = ref(false)
const showPatternRepeatOriginInfo = ref(false)
const showPatternHorizontalOffsetInfo = ref(false)
const showPatternVerticalOffsetInfo = ref(false)
const showPatternRepeatInfo = ref(false)
const showPatternBlendInfo = ref(false)

const showMaskInfo = ref(false)
const showMaskColorInfo = ref(false)
const showMaskTransformInfo = ref(false)
const showMaskSizeInfo = ref(false)
const showMaskWidthInfo = ref(false)
const showMaskHeightInfo = ref(false)
const showMaskPositionInfo = ref(false)
const showMaskAspectRatioInfo = ref(false)
const showMaskBlendInfo = ref(false)

const showVideoLoopInfo = ref(false)
const showVideoMuteInfo = ref(false)
const showVideoOverlayInfo = ref(false)
const showParallaxInfo = ref(false)
const showParallaxMethodInfo = ref(false)

const getInfoContent = (baseKey: string) => {
    const moduleType = props.module?.type || 'common'
    const key = baseKey.includes('.') ? baseKey.split('.').pop() : baseKey
    
    // Key paths to try in order
    const paths = [
        `builder.info.${moduleType}.${key}`,
        `builder.info.common.${key}`,
        `builder.info.${key}`
    ]

    for (const path of paths) {
        const text = t(path)
        if (text && text !== path) {
            return text
        }
    }
    
    return null
}

const openResponsiveModal = (label: string, baseKey: string, type: string = 'color', options: unknown[] | Record<string, unknown> | null = null, subFields: SubFieldGroup[] = []) => {
    responsiveModal.value = {
        show: true,
        label,
        baseKey,
        type,
        options: options || undefined,
        subFields
    }
}

// Computed module settings to ensure reactivity
const moduleSettings = computed(() => {
    // Explicitly track blocks to ensure reactivity
    if (builder?.blocks) { void builder.blocks }
    
    const currentModule = builder?.findModule(props.module.id)
    return currentModule?.settings || {}
})

const tabs = computed(() => [
    { id: 'color', label: t('builder.fields.background.tabs.color'), icon: PaintBucket },
    { id: 'gradient', label: t('builder.fields.background.tabs.gradient'), icon: Component },
    { id: 'image', label: t('builder.fields.background.tabs.image'), icon: Image },
    { id: 'video', label: t('builder.fields.background.tabs.video'), icon: Video },
    { id: 'pattern', label: t('builder.fields.background.tabs.pattern'), icon: Grid3X3 },
    { id: 'mask', label: t('builder.fields.background.tabs.mask'), icon: Moon },
])

const patternSizeOptions = computed(() => [
    { label: t('builder.fields.background.image.size.options.actual'), value: 'actual' },
    { label: t('builder.fields.background.image.size.options.cover'), value: 'cover' },
    { label: t('builder.fields.background.image.size.options.fit'), value: 'fit' },
    { label: t('builder.fields.background.image.size.options.stretch'), value: 'stretch' },
    { label: t('builder.fields.background.image.size.options.custom'), value: 'custom' }
])

const patternRepeatOptions = computed(() => [
    { label: t('builder.fields.background.image.repeat.options.repeat'), value: 'repeat' },
    { label: t('builder.fields.background.image.repeat.options.repeatX'), value: 'repeat-x' },
    { label: t('builder.fields.background.image.repeat.options.repeatY'), value: 'repeat-y' },
    { label: t('builder.fields.background.image.repeat.options.space'), value: 'space' },
    { label: t('builder.fields.background.image.repeat.options.round'), value: 'round' },
    { label: t('builder.fields.background.image.repeat.options.noRepeat'), value: 'no-repeat' }
])

const patternRepeatOriginOptions = computed(() => [
    { label: t('builder.fields.background.pattern.repeatOrigin.options.topLeft'), value: 'left top' },
    { label: t('builder.fields.background.pattern.repeatOrigin.options.topCenter'), value: 'center top' },
    { label: t('builder.fields.background.pattern.repeatOrigin.options.topRight'), value: 'right top' },
    { label: t('builder.fields.background.pattern.repeatOrigin.options.centerLeft'), value: 'left center' },
    { label: t('builder.fields.background.pattern.repeatOrigin.options.center'), value: 'center center' },
    { label: t('builder.fields.background.pattern.repeatOrigin.options.centerRight'), value: 'right center' },
    { label: t('builder.fields.background.pattern.repeatOrigin.options.bottomLeft'), value: 'left bottom' },
    { label: t('builder.fields.background.pattern.repeatOrigin.options.bottomCenter'), value: 'center bottom' },
    { label: t('builder.fields.background.pattern.repeatOrigin.options.bottomRight'), value: 'right bottom' }
])

const imagePositionOptions = computed(() => [
    { label: t('builder.fields.background.image.position.options.topLeft'), value: 'left top' },
    { label: t('builder.fields.background.image.position.options.topCenter'), value: 'center top' },
    { label: t('builder.fields.background.image.position.options.topRight'), value: 'right top' },
    { label: t('builder.fields.background.image.position.options.centerLeft'), value: 'left center' },
    { label: t('builder.fields.background.image.position.options.center'), value: 'center center' },
    { label: t('builder.fields.background.image.position.options.centerRight'), value: 'right center' },
    { label: t('builder.fields.background.image.position.options.bottomLeft'), value: 'left bottom' },
    { label: t('builder.fields.background.image.position.options.bottomCenter'), value: 'center bottom' },
    { label: t('builder.fields.background.image.position.options.bottomRight'), value: 'right bottom' }
])

const activePattern = computed(() => {
    const val = getResolvedValue('backgroundPattern')
    if (!val || val === 'none') return null
    return BackgroundPatterns.find(p => p.id === val)
})



const patternOptions = computed(() => {
    return BackgroundPatterns.map(p => ({ 
        label: t('builder.fields.background.pattern.options.' + p.id), 
        value: p.id 
    }))
})

const initDefaultPattern = () => {
   const first = BackgroundPatterns[0]?.id || 'polka-dots'
   updateSetting('backgroundPattern', first)
}

const initDefaultMask = () => {
    const first = BackgroundMasks[0]?.id || 'arch'
    updateSetting('backgroundMask', first)
}

const clearPattern = () => {
    updateSetting('backgroundPattern', 'none')
}



const blendModeOptions = computed(() => [
    { label: t('builder.settings.options.blend_mode.normal'), value: 'normal' },
    { label: t('builder.settings.options.blend_mode.multiply'), value: 'multiply' },
    { label: t('builder.settings.options.blend_mode.screen'), value: 'screen' },
    { label: t('builder.settings.options.blend_mode.overlay'), value: 'overlay' },
    { label: t('builder.settings.options.blend_mode.darken'), value: 'darken' },
    { label: t('builder.settings.options.blend_mode.lighten'), value: 'lighten' },
    { label: t('builder.settings.options.blend_mode.color-dodge'), value: 'color-dodge' },
    { label: t('builder.settings.options.blend_mode.color-burn'), value: 'color-burn' },
    { label: t('builder.settings.options.blend_mode.hard-light'), value: 'hard-light' },
    { label: t('builder.settings.options.blend_mode.soft-light'), value: 'soft-light' },
    { label: t('builder.settings.options.blend_mode.difference'), value: 'difference' },
    { label: t('builder.settings.options.blend_mode.exclusion'), value: 'exclusion' },
    { label: t('builder.settings.options.blend_mode.hue'), value: 'hue' },
    { label: t('builder.settings.options.blend_mode.saturation'), value: 'saturation' },
    { label: t('builder.settings.options.blend_mode.color'), value: 'color' },
    { label: t('builder.settings.options.blend_mode.luminosity'), value: 'luminosity' }
])

const maskAspectRatioOptions = computed(() => [
    { label: t('builder.fields.background.mask.aspectRatio.options.square'), value: 'square', icon: Square },
    { label: t('builder.fields.background.mask.aspectRatio.options.landscape'), value: 'landscape', icon: RectangleHorizontal },
    { label: t('builder.fields.background.mask.aspectRatio.options.portrait'), value: 'portrait', icon: RectangleVertical }
])



const activeMask = computed(() => {
    const val = getResolvedValue<string | null>('backgroundMask')
    if (!val || val === 'none') return null
    return BackgroundMasks.find(m => m.id === val)
})

const maskOptions = computed(() => {
    return [
        { label: t('builder.common.none'), value: 'none' },
        ...BackgroundMasks.map(m => ({ label: m.label, value: m.id }))
    ]
})

// Add device prop for strict mode rendering
// (device prop added to main defineProps above)

const currentDevice = computed(() => {
    // If explicit device prop is passed (e.g. from ResponsiveFieldModal), use it.
    if (props.device) return props.device
    // Otherwise fallback to global builder device
    return builder?.device.value || 'desktop'
})

const getResponsiveValue = <T = unknown>(baseKey: string): T => {
    // builder?.device.value is a string (primitive) if accessed via useBuilder's return, 
    // but here we are using the computed wrapper locally.
    const dev = currentDevice.value
    const suffix = dev === 'desktop' ? '' : (dev === 'mobile' ? '_mobile' : `_${dev}`)
    const val = moduleSettings.value[baseKey + suffix]
    
    // Return empty string if undefined/null or empty object
    const isEmpty = val === undefined || val === null || (typeof val === 'object' && val !== null && !Array.isArray(val) && Object.keys(val).length === 0)
    return isEmpty ? '' as unknown as T : val as T
}

const getResolvedValue = <T = unknown>(baseKey: string): T => {
    const val = getResponsiveValue<T>(baseKey)
    if ((val as unknown) === '' && currentDevice.value !== 'desktop') {
        const placeholder = getResponsivePlaceholder<T>(baseKey)
        return (placeholder === undefined || placeholder === null) ? '' as unknown as T : placeholder
    }
    return val
}

const hasOverride = (baseKey: string) => {
    if (currentDevice.value === 'desktop') return false
    const suffix = currentDevice.value === 'mobile' ? '_mobile' : (currentDevice.value === 'tablet' ? '_tablet' : '')
    const val = moduleSettings.value[baseKey + suffix]
    return val !== undefined && val !== null && val !== ''
}



const getResponsivePlaceholder = <T = unknown>(baseKey: string): T => {
    if (currentDevice.value === 'desktop') return '' as unknown as T
    const settings = moduleSettings.value
    const desktopValue = settings[baseKey] || ''
    const tabletValue = settings[baseKey + '_tablet']
    
    // In builder logic, '' means inherited.
    if (currentDevice.value === 'tablet') return desktopValue as T
    
    // For phone/mobile: if tablet has value, use it. Otherwise use desktop.
    if (tabletValue !== undefined && tabletValue !== null && tabletValue !== '') return tabletValue as T
    return desktopValue as T
}

const getSyncedDimensionData = (key: string, value: string | number | null, suffix: string) => {
    const finalData: Record<string, string | number | null> = { [key + suffix]: value }
    const syncMap: Record<string, string> = {
        'backgroundImageWidth': 'backgroundImageHeight',
        'backgroundImageHeight': 'backgroundImageWidth',
        'backgroundVideoWidth': 'backgroundVideoHeight',
        'backgroundVideoHeight': 'backgroundVideoWidth',
        'backgroundPatternWidth': 'backgroundPatternHeight',
        'backgroundPatternHeight': 'backgroundPatternWidth',
        'backgroundMaskWidth': 'backgroundMaskHeight',
        'backgroundMaskHeight': 'backgroundMaskWidth'
    }

    // Proportional logic
    const baseKey = key.replace(/Width|Height/, '')
    const propKey = baseKey + 'Proportional'
    const propVal = getResolvedValue(propKey)
    const isProportional = propVal === true || propVal === 'true' || propVal === 1
    
    if (isProportional && syncMap[key]) {
        const otherKey = syncMap[key]
        if (value === '' || value === 'auto') {
            finalData[otherKey + suffix] = value
            return finalData
        }

        const newValue = parseFloat(String(value))
        if (isNaN(newValue)) return finalData

        let currentW = parseFloat(String(getResolvedValue(baseKey + 'Width')))
        let currentH = parseFloat(String(getResolvedValue(baseKey + 'Height')))
        
        // If one is auto/NaN/0, treat it as 1:1 relative to the other
        if (isNaN(currentW) || currentW <= 0) currentW = !isNaN(currentH) && currentH > 0 ? currentH : newValue
        if (isNaN(currentH) || currentH <= 0) currentH = !isNaN(currentW) && currentW > 0 ? currentW : newValue

        const unit = typeof value === 'string' ? value.replace(/[0-9.]/g, '') : 'px'
        let otherValue
        if (key.includes('Width')) {
            // Width changed, update Height: H2 = (H1/W1) * W2
            otherValue = currentW > 0 ? Math.round((currentH / currentW) * newValue) : newValue
        } else {
            // Height changed, update Width: W2 = (W1/H1) * H2
            otherValue = currentH > 0 ? Math.round((currentW / currentH) * newValue) : newValue
        }
        
        finalData[otherKey + suffix] = otherValue + (unit || 'px')
        
        return finalData
    }

    if (syncMap[key] && typeof value === 'string' && value !== 'auto') {
        const unit = value.replace(/[0-9.]/g, '') || 'px'
        const otherKey = syncMap[key]
        const otherValue = getResolvedValue(otherKey)
        
        if (otherValue && typeof otherValue === 'string' && otherValue !== 'auto') {
            const otherNum = parseFloat(otherValue) || 0
            const newOtherValue = `${otherNum}${unit}`
            finalData[otherKey + suffix] = newOtherValue
        }
    }
    return finalData
}

const colorPreviewStyle = computed(() => {
    void lastUpdate.value
    const val = getResolvedValue<string>('backgroundColor')
    return {
        backgroundColor: val || 'transparent'
    }
})

const gradientPreviewStyle = computed(() => {
    // Track moduleSettings for reactivity
    void lastUpdate.value
    const gradient = getResolvedValue<Gradient>('backgroundGradient')
    if (!gradient || (typeof gradient === 'object' && !('stops' in gradient))) return {}

    return {
        background: generateGradientCSS(gradient)
    }
})

const imagePreviewStyle = computed(() => {
    // Track moduleSettings for reactivity
    void lastUpdate.value
    const imageUrl = getResolvedValue('backgroundImage')
    if (!imageUrl) return {}

    const styles: Record<string, string | number | undefined> = {
        backgroundImage: `url(${imageUrl})`,
        backgroundRepeat: getResolvedValue<string>('backgroundImageRepeat') || 'no-repeat',
        backgroundPosition: getResolvedValue<string>('backgroundImagePosition') || 'center'
    }

    let size = (getResolvedValue('backgroundImageSize') as string) || 'cover'
    if (size === 'custom') {
        const w = (getResolvedValue('backgroundImageWidth') as string) || 'auto'
        const h = (getResolvedValue('backgroundImageHeight') as string) || 'auto'
        size = `${w} ${h}`
    } else if (size === 'stretch') {
        size = '100% 100%'
    } else if (size === 'fit') {
        size = 'contain'
    }

    styles.backgroundSize = size

    return styles
})

const videoPreviewStyle = computed(() => {
    const styles: Record<string, string | number | undefined> = {}
    const w = getResolvedValue<string | number>('backgroundVideoWidth')
    const h = getResolvedValue<string | number>('backgroundVideoHeight')
    
    // For videos, we apply the dimension directly to the element
    // but we constrain it to the 120px box
    if (w && typeof w !== 'object') styles.width = w
    if (h && typeof h !== 'object') styles.height = h
    
    styles.maxWidth = '100%'
    styles.maxHeight = '100%'
    
    if (w || h) {
        styles.objectFit = 'contain'
    } else {
        styles.objectFit = 'cover'
        styles.width = '100%'
        styles.height = '100%'
    }
    
    return styles
})

const lastUpdate = ref(0)

const combinedBackgroundStyle = computed(() => {
    // Explicitly track moduleSettings and lastUpdate for reactivity
    void moduleSettings.value
    void lastUpdate.value
    
    // List of all background-related keys to resolve
    const backgroundKeys = [
        'backgroundColor', 'backgroundGradient', 'backgroundGradients', 'backgroundGradientShowAboveImage',
        'backgroundImage', 'backgroundImageSize', 'backgroundImageWidth', 'backgroundImageHeight',
        'backgroundImageRepeat', 'backgroundImagePosition', 'backgroundImageBlendMode',
        'backgroundPattern', 'backgroundPatternColor', 'backgroundPatternRotate', 'backgroundPatternInvert',
        'backgroundPatternFlipH', 'backgroundPatternFlipV', 'backgroundPatternSize',
        'backgroundPatternWidth', 'backgroundPatternHeight', 'backgroundPatternRepeat',
        'backgroundPatternRepeatOrigin', 'backgroundPatternBlendMode',
        'backgroundMask', 'backgroundMaskColor', 'backgroundMaskRotate', 'backgroundMaskInvert',
        'backgroundMaskFlipH', 'backgroundMaskFlipV', 'backgroundMaskAspectRatio',
        'backgroundMaskSize', 'backgroundMaskWidth', 'backgroundMaskHeight',
        'backgroundMaskRepeat', 'backgroundMaskRepeatOrigin', 'backgroundMaskBlendMode'
    ]
    
    const resolvedSettings: Record<string, unknown> = {}
    backgroundKeys.forEach(key => {
        resolvedSettings[key] = getResolvedValue(key)
    })
    
    return getBackgroundStyles(resolvedSettings)
})

const updateSetting = (key: string, value: string | number | boolean | object | null) => {
    // If key is already responsive-specific (has suffix), use it directly
    if (key.includes('_tablet') || key.includes('_mobile')) {
        const baseKey = key.replace(/(_tablet|_mobile)$/, '')
        const match = key.match(/(_tablet|_mobile)$/)
        const suffix = match ? match[0] : ''
        const syncedData = getSyncedDimensionData(baseKey, value as string | number | null, suffix)
        builder?.updateModuleSettings(props.module.id, syncedData)
        lastUpdate.value++
        return
    }

    // Otherwise, append suffix based on current device
    const suffix = currentDevice.value === 'desktop' ? '' : (currentDevice.value === 'mobile' ? '_mobile' : `_${currentDevice.value}`)
    const syncedData = getSyncedDimensionData(key, value as string | number | null, suffix)

    builder?.updateModuleSettings(props.module.id, syncedData)
    lastUpdate.value++
}

const focusColorInput = () => {
    if (colorFieldRef.value) {
        colorFieldRef.value.openPicker()
    }
}

const clearBackgroundColor = () => {
    updateSetting('backgroundColor', '')
}

const resetBackgroundColor = () => {
    updateSetting('backgroundColor', '')
}

const handleResponsiveUpdate = (data: Record<string, unknown>) => {
    const finalData: Record<string, unknown> = {}
    const currentSettings = builder?.findModule(props.module.id)?.settings || {}
    
    Object.entries(data).forEach(([key, value]) => {
        lastUpdate.value++
        if (key.includes('.')) {
            // key is like "backgroundGradient_mobile.direction"
            const [parentPath, childKey] = key.split('.')
            let existingParent = currentSettings[parentPath]
            if (!existingParent || (typeof existingParent === 'object' && Object.keys(existingParent).length === 0)) {
                const baseKey = parentPath.replace(/(_tablet|_mobile)$/, '')
                existingParent = currentSettings[baseKey] || {}
            }
            (finalData as Record<string, unknown>)[parentPath] = { ...(existingParent as object), [childKey]: value }
        } else {
            // Flat keys - Check for unit sync
            const baseKey = key.replace(/(_tablet|_mobile)$/, '')
            const suffix = key.match(/(_tablet|_mobile)$/)?.[0] || ''
            const syncedData = getSyncedDimensionData(baseKey, value as string | number | null, suffix)
            Object.assign(finalData, syncedData)
        }
    })

    builder?.updateModuleSettings(props.module.id, finalData as Record<string, unknown>)
}

const initDefaultGradient = () => {
    // 1. Check current Background Color first
    let baseColor = getResponsiveValue<string>('backgroundColor')
    
    if (!baseColor) {
        // 2. Fallback to Global Variables (Primary Color)
        const colors = builder?.globalVariables?.globalColors.value as { name: string; value: string }[] || []
        baseColor = colors.find((c: { name: string; value: string }) => c.name.toLowerCase().includes('primary'))?.value as string
    }

    if (!baseColor) {
        // 3. Fallback to Builder Theme / Sample Modern Blue
        baseColor = '#2EA3F2' 
    }

    // Generate a harmonious modern gradient pair
    const [c1, c2] = getHarmoniousGradientColors(baseColor)

    const newGradient: Gradient = {
        type: 'linear',
        direction: '180deg',
        repeat: false,
        length: '100%',
        stops: [
            { color: c1, position: 0 },
            { color: c2, position: 100 }
        ]
    }

    // Set as the primary gradient (multi-layer support uses backgroundGradients array if needed)
    updateSetting('backgroundGradient', newGradient)
}




</script>

<style scoped>
.background-field {
    margin-bottom: 24px;
}

.bg-tabs {
    display: flex;
    border-bottom: 1px solid var(--builder-border);
    margin-bottom: 16px;
}

.bg-tab-item {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    cursor: pointer;
    color: var(--builder-text-muted);
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}

.bg-tab-item:hover {
    color: var(--builder-text-primary);
    background-color: var(--builder-bg-secondary);
}

.bg-tab-item.active {
    color: var(--builder-accent);
    border-bottom-color: var(--builder-accent);
}

.field-info-panel {
    background-color: var(--builder-bg-secondary);
    border-left: 3px solid var(--builder-accent);
    padding: 10px 14px;
    margin-bottom: 12px;
    font-size: 11px;
    line-height: 1.6;
    color: var(--builder-text-secondary);
    border-radius: 0 4px 4px 0;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

.bg-header {
    font-size: 13px;
    font-weight: 500;
    color: var(--builder-accent);
    margin-bottom: 12px;
}

.bg-section {
    animation: fadeIn 0.2s ease;
}

.color-preview-box {
    cursor: crosshair;
}

.bg-preview-box {
    position: relative;
    width: 100%;
    min-height: 120px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    margin-bottom: 8px;
}

.image-preview-box {
    background-color: var(--builder-bg-primary);
}

.color-preview {
    width: 100%;
    height: 100%;
    position: relative;
    box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
}

.image-preview {
    width: 100%;
    height: 100%;
    position: relative;
    background-size: cover;
    background-position: center;
}

/* Action buttons overlay */
.preview-actions {
    position: absolute;
    top: 8px;
    right: 8px;
    display: flex;
    gap: 4px;
    z-index: 10;
}

.placeholder-box {
    border: 2px dashed var(--builder-border);
    border-radius: 6px;
    padding: 32px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--builder-text-muted);
    font-size: 12px;
}

.placeholder-notice {
    font-size: 12px;
    color: var(--builder-text-muted);
    font-style: italic;
    padding: 12px;
    border: 1px solid var(--builder-border);
    border-radius: 4px;
}

.mt-4 { margin-top: 16px; }
.space-y-4 > * + * { margin-top: 16px; }
.mb-2 { margin-bottom: 8px; }
.mb-4 { margin-bottom: 16px; }

.field-info-text {
    font-size: 12px;
    color: var(--builder-text-secondary);
    line-height: 1.5;
    padding: 0 2px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(2px); }
    to { opacity: 1; transform: translateY(0); }
}

.select-field-container {
    width: 100%;
}

.select-field-container:deep(.base-dropdown-wrapper) {
    display: block;
    width: 100%;
}

.select-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    height: 38px;
    padding: 0 12px;
    background-color: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
    color: var(--builder-text-primary);
}

.select-trigger:hover, .select-trigger.is-open {
    border-color: var(--builder-accent, #2563eb);
    background-color: var(--builder-bg-secondary);
}

.select-arrow {
    color: var(--builder-text-muted);
    transition: transform 0.2s;
}

.is-open .select-arrow {
    transform: rotate(180deg);
}

.selected-mask-preview {
    width: 24px;
    height: 24px;
    background: var(--builder-bg-primary);
    border-radius: 2px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.bg-preview-box:hover {
    border-color: var(--builder-accent);
}

.bg-preview-box.is-empty {
    border-style: dashed;
}

.preview-bg-image {
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.preview-bg-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.empty-preview {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: var(--builder-text-muted);
    font-size: 11px;
}


.inherited-badge {
    position: absolute;
    bottom: 8px;
    left: 8px;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    pointer-events: none;
    z-index: 5;
}

</style>

<!-- Non-scoped styles for teleported dropdown content -->
<style>
.dropdown-preview-container {
    background: var(--builder-bg-background);
    width: 220px;
    padding: 2px !important;
    box-sizing: border-box;
    overflow-x: hidden;
}

/* Ensure Pattern and Mask grid styles reach into teleported Popover */
.dropdown-preview-container .pattern-field,
.dropdown-preview-container .mask-field {
    padding: 0 !important;
    width: 100% !important;
}

.dropdown-preview-container .patterns-grid,
.dropdown-preview-container .masks-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 2px !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    box-sizing: border-box !important;
}

.dropdown-preview-container .pattern-item,
.dropdown-preview-container .mask-item {
    aspect-ratio: 1 / 1 !important;
    width: 100% !important;
    height: auto !important;
    padding: 0 !important;
    border-radius: 4px !important;
    box-sizing: border-box !important;
    overflow: hidden;
    border: 1px solid var(--builder-border) !important;
}

.dropdown-preview-container .pattern-preview,
.dropdown-preview-container .mask-preview {
    width: 100% !important;
    height: 100% !important;
    padding: 2px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-sizing: border-box !important;
}

.dropdown-preview-container .pattern-svg,
.dropdown-preview-container .mask-svg,
.dropdown-preview-container svg {
    width: 100% !important;
    height: 100% !important;
    object-fit: contain !important;
    display: block;
}
</style>
