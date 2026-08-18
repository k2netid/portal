<template>
  <node-view-wrapper 
    as="span" 
    class="inline-block align-middle select-none mx-0.5 leading-none relative group"
    :class="{ 'ring-1 ring-primary rounded-sm': selected }"
    :style="wrapperStyle"
  >
    <component 
      :is="iconComponent" 
      :size="sizeValue"
      :stroke-width="node.attrs.strokeWidth"
      class="transition-transform"
      :style="iconStyle"
    />

    <!-- Resize Handle (Only visible when selected) -->
    <div 
      v-if="selected"
      class="absolute -right-1 -bottom-1 w-3 h-3 bg-primary border border-white rounded-full cursor-se-resize z-10"
      @mousedown.stop.prevent="startResize"
    />
  </node-view-wrapper>
</template>

<script setup lang="ts">
import { computed, ref, onUnmounted } from 'vue';
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3';
import {
  AlertCircle,
  AlertTriangle,
  ArrowRight,
  AtSign,
  Bell,
  Calendar,
  Camera,
  Check,
  CheckCircle2,
  ChevronRight,
  Circle,
  Clock,
  Columns,
  Copy,
  Download,
  Edit3,
  ExternalLink,
  Eye,
  EyeOff,
  Film,
  Filter,
  Flag,
  Grid,
  GripHorizontal,
  Headphones,
  Heart,
  HelpCircle,
  Image,
  Info,
  Key,
  Languages,
  Layers,
  Layout,
  Lock,
  LogOut,
  Mail,
  MapPin,
  Maximize2,
  Menu,
  MessageCircle,
  MessageSquare,
  Mic,
  Minimize2,
  MoreHorizontal,
  Music,
  Paperclip,
  Phone,
  Play,
  PlusCircle,
  Power,
  RefreshCw,
  Rows,
  Save,
  Search,
  Send,
  Settings,
  Share2,
  Shield,
  ShieldAlert,
  ShieldCheck,
  Sliders,
  Speaker,
  Star,
  Trash2,
  Upload,
  User,
  Video,
  Volume2,
  X,
  XCircle,
  Zap,
} from 'lucide-vue-next';
import type { Component } from 'vue';

const iconMap: Record<string, Component> = {
  Circle, Zap, Star, Heart, HelpCircle, Info, Check, X, 
  ArrowRight, ChevronRight, Mail, MessageSquare, Image, 
  Video, Play, Settings, Search, Menu, Grid, Trash2, 
  Edit3, LogOut, ExternalLink, AlertCircle, Shield, 
  PlusCircle, Lock, User, Bell, Calendar, Camera, 
  Clock, MapPin, Flag, Key, Eye, EyeOff, Maximize2, 
  Minimize2, Rows, Columns, Layout, Share2, Download, 
  Upload, RefreshCw, Paperclip, Send, AtSign, Languages, 
  Phone, MessageCircle, Headphones, Speaker, Volume2, 
  Film, Music, Mic, Sliders, MoreHorizontal, GripHorizontal, 
  Layers, Filter, Save, Copy, Power, ShieldCheck, 
  ShieldAlert, AlertTriangle, CheckCircle2, XCircle
};

const props = defineProps(nodeViewProps)

const iconComponent = computed(() => {
    const name = props.node.attrs.name;
    return iconMap[name] || iconMap.Circle;
});

// Computed Styles
const wrapperStyle = computed(() => ({
    backgroundColor: props.node.attrs.backgroundColor,
    borderRadius: props.node.attrs.borderRadius,
    padding: props.node.attrs.padding,
    transform: `rotate(${props.node.attrs.rotate}deg)`,
    opacity: props.node.attrs.opacity,
}));

const iconStyle = computed(() => ({
    color: props.node.attrs.color,
    width: props.node.attrs.size,
    height: props.node.attrs.size,
}));

const sizeValue = computed(() => {
    // If size is '1em' or string, pass it to style, but Lucide prop expects number or string.
    return props.node.attrs.size;
});

// Resize Logic
const isResizing = ref(false);
const startX = ref(0);
const startWidth = ref(0);

const startResize = (event: MouseEvent) => {
    isResizing.value = true;
    startX.value = event.clientX;
    
    // Parse current size to pixels if possible, or approximate
    // Limit resizing to pixel values for consistency during drag
    const currentSize = props.node.attrs.size;
    
    if (typeof currentSize === 'string' && currentSize.endsWith('px')) {
        startWidth.value = parseInt(currentSize);
    } else if (typeof currentSize === 'string' && currentSize.endsWith('em')) {
         // Convert em to approx px for resizing interaction (assuming 16px base)
         // This is a rough approximation to start the drag
         startWidth.value = parseFloat(currentSize) * 16;
    } else {
        startWidth.value = 24; // Default fallback
    }

    document.addEventListener('mousemove', onResize);
    document.addEventListener('mouseup', stopResize);
};

const onResize = (event: MouseEvent) => {
    if (!isResizing.value) return;
    
    const diff = event.clientX - startX.value;
    const newSize = Math.max(12, startWidth.value + diff); // Min 12px
    
    props.updateAttributes({
        size: `${Math.round(newSize)}px`
    });
};

const stopResize = () => {
    isResizing.value = false;
    document.removeEventListener('mousemove', onResize);
    document.removeEventListener('mouseup', stopResize);
};

onUnmounted(() => {
    document.removeEventListener('mousemove', onResize);
    document.removeEventListener('mouseup', stopResize);
});
</script>
