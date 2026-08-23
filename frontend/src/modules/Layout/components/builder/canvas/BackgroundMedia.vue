<template>
  <div v-if="hasVideo" class="background-video-container">
    <video
      ref="videoRef"
      class="background-video"
      :src="videoUrl"
      :muted="isMuted"
      :loop="isLoop"
      :autoplay="true"
      :style="videoStyles"
      playsinline
      @loadeddata="onVideoLoaded"
    ></video>
    
    <!-- Video Overlay -->
    <div 
      v-if="overlayColor" 
      class="video-overlay" 
      :style="{ backgroundColor: overlayColor }"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { computed, ref, type CSSProperties } from 'vue';

interface Settings {
    backgroundVideoMp4?: string;
    backgroundVideoWebm?: string;
    backgroundVideoMute?: boolean;
    backgroundVideoLoop?: boolean;
    backgroundVideoOverlayColor?: string;
    backgroundVideoWidth?: string;
    backgroundVideoHeight?: string;
    backgroundVideoPauseOnPlay?: boolean;
}

const props = defineProps<{
  settings: Settings
}>();

const videoRef = ref<HTMLVideoElement | null>(null);

const hasVideo = computed(() => {
  return !!(props.settings.backgroundVideoMp4 || props.settings.backgroundVideoWebm);
});

const videoUrl = computed(() => {
  return props.settings.backgroundVideoWebm || props.settings.backgroundVideoMp4;
});

const isMuted = computed(() => {
  return props.settings.backgroundVideoMute !== false; // Default to muted for autoplay
});

const isLoop = computed(() => {
  return props.settings.backgroundVideoLoop !== false; // Default to loop
});

const overlayColor = computed(() => {
  return props.settings.backgroundVideoOverlayColor || '';
});

const videoStyles = computed<CSSProperties>(() => {
    const s = props.settings;
    const styles: CSSProperties = {};
    
    if (s.backgroundVideoWidth) {
        styles.width = s.backgroundVideoWidth;
        styles.minWidth = '0'; // Override the 100% min-width in CSS
    }
    
    if (s.backgroundVideoHeight) {
        styles.height = s.backgroundVideoHeight;
        styles.minHeight = '0'; // Override the 100% min-height in CSS
    }
    
    // If either width or height is customized, we switch to contain to prevent distortion
    // unless the user wants something else. But cover is the default for background.
    if (s.backgroundVideoWidth || s.backgroundVideoHeight) {
        styles.objectFit = 'contain';
    } else {
        styles.objectFit = 'cover';
    }
    
    return styles;
});

const onVideoLoaded = () => {
    if (videoRef.value) {
        videoRef.value.play().catch(err => {
            logger.warning('Background video autoplay failed:', err);
        });
    }
};


</script>

<style scoped>
.background-video-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 0; /* Base layer */
  pointer-events: none;
}

.background-video {
  position: absolute;
  top: 50%;
  left: 50%;
  min-width: 100%;
  min-height: 100%;
  width: auto;
  height: auto;
  transform: translate(-50%, -50%);
  object-fit: cover;
}

.video-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
}
</style>
