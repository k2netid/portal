<template>
  <div
    class="spinner-container"
    :class="[size, color, { 'inline': inline }]"
  >
    <svg
      v-if="type === 'circular'"
      class="spinner-circular"
      :class="[size]"
      viewBox="0 0 50 50"
    >
      <circle
        class="spinner-path"
        cx="25"
        cy="25"
        r="20"
        fill="none"
        stroke-width="4"
      />
    </svg>
    
    <div
      v-else-if="type === 'dots'"
      class="spinner-dots"
    >
      <span />
      <span />
      <span />
    </div>
    
    <div
      v-else-if="type === 'bars'"
      class="spinner-bars"
    >
      <div />
      <div />
      <div />
      <div />
    </div>
    
    <div
      v-if="text"
      class="spinner-text"
    >
      {{ text }}
    </div>
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  type?: 'circular' | 'dots' | 'bars';
  size?: 'sm' | 'md' | 'lg' | 'xl';
  color?: 'primary' | 'white' | 'gray';
  text?: string;
  inline?: boolean;
}>(), {
  type: 'circular',
  size: 'md',
  color: 'primary',
  text: '',
  inline: false,
});
</script>

<style scoped>
.spinner-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.spinner-container.inline {
  display: inline-flex;
}

/* Circular Spinner */
.spinner-circular {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.spinner-circular.sm {
  width: 1rem;
  height: 1rem;
}

.spinner-circular.md {
  width: 2rem;
  height: 2rem;
}

.spinner-circular.lg {
  width: 3rem;
  height: 3rem;
}

.spinner-circular.xl {
  width: 4rem;
  height: 4rem;
}

.spinner-path {
  stroke: currentColor;
  stroke-linecap: round;
  stroke-dasharray: 90, 150;
  stroke-dashoffset: 0;
  animation: spinner-dash 1.5s ease-in-out infinite;
}

.spinner-path.primary {
  color: rgb(37 99 235);
}

:global(.dark) .spinner-path.primary {
  color: rgb(96 165 250);
}

.spinner-path.white {
  color: white;
}

.spinner-path.gray {
  color: rgb(75 85 99);
}

:global(.dark) .spinner-path.gray {
  color: rgb(156 163 175);
}

@keyframes spinner-dash {
  0% {
    stroke-dasharray: 1, 150;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -35;
  }
  100% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -124;
  }
}

/* Dots Spinner */
.spinner-dots {
  display: flex;
  gap: 0.25rem;
}

.spinner-dots.sm span {
  width: 0.25rem;
  height: 0.25rem;
}

.spinner-dots.md span {
  width: 0.5rem;
  height: 0.5rem;
}

.spinner-dots.lg span {
  width: 0.75rem;
  height: 0.75rem;
}

.spinner-dots span {
  background-color: rgb(37 99 235);
  border-radius: 9999px;
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  animation-delay: calc(var(--i) * 0.2s);
}

:global(.dark) .spinner-dots span {
  background-color: rgb(96 165 250);
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.spinner-dots span:nth-child(1) {
  --i: 0;
}

.spinner-dots span:nth-child(2) {
  --i: 1;
}

.spinner-dots span:nth-child(3) {
  --i: 2;
}

/* Bars Spinner */
.spinner-bars {
  display: flex;
  gap: 0.25rem;
  align-items: flex-end;
}

.spinner-bars.sm div {
  width: 0.25rem;
}

.spinner-bars.md div {
  width: 0.375rem;
}

.spinner-bars.lg div {
  width: 0.5rem;
}

.spinner-bars div {
  background-color: rgb(37 99 235);
  border-top-left-radius: 0.25rem;
  border-top-right-radius: 0.25rem;
  animation: spinner-bars 1.2s ease-in-out infinite;
}

:global(.dark) .spinner-bars div {
  background-color: rgb(96 165 250);
}

.spinner-bars div:nth-child(1) {
  height: 20%;
  animation-delay: 0s;
}

.spinner-bars div:nth-child(2) {
  height: 40%;
  animation-delay: 0.1s;
}

.spinner-bars div:nth-child(3) {
  height: 60%;
  animation-delay: 0.2s;
}

.spinner-bars div:nth-child(4) {
  height: 80%;
  animation-delay: 0.3s;
}

@keyframes spinner-bars {
  0%, 40%, 100% {
    transform: scaleY(0.4);
  }
  20% {
    transform: scaleY(1);
  }
}

.spinner-text {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  line-height: 1.25rem;
  color: rgb(75 85 99);
}

:global(.dark) .spinner-text {
  color: rgb(156 163 175);
}
</style>

