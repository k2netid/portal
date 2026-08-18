import { render, h } from 'vue';
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

export function useIconHydration() {
    const hydrateIcons = (container: HTMLElement | { $el?: unknown } | null) => {
        const root = container instanceof HTMLElement
            ? container
            : (container && '$el' in container && container.$el instanceof HTMLElement ? container.$el : null);

        if (!root) return;

        const icons = root.querySelectorAll('span[data-type="icon"]');

        icons.forEach(el => {
            if (!(el instanceof HTMLElement)) return;

            // Check if already hydrated
            if (el.dataset.hydrated) return;

            const name = el.getAttribute('name');
            if (!name) return;

            const IconComponent = iconMap[name] || iconMap.Circle;

            // Collect styles from attributes
            // Note: attributes in HTML might be lowercase, but Tiptap should preserve them if possible
            // We'll check standard names
            const size = el.getAttribute('size') || '1em';
            const color = el.getAttribute('color') || 'currentColor';
            const strokeWidth = el.getAttribute('strokeWidth') || el.getAttribute('strokewidth') || 2;
            const rotate = el.getAttribute('rotate') || 0;
            const backgroundColor = el.getAttribute('backgroundColor') || el.getAttribute('backgroundcolor');
            const borderRadius = el.getAttribute('borderRadius') || el.getAttribute('borderradius') || '0px';
            const padding = el.getAttribute('padding') || '0px';
            const opacity = el.getAttribute('opacity') || 1;

            // Apply wrapper styles to the span itself
            el.style.display = 'inline-block';
            el.style.verticalAlign = 'middle';
            el.style.lineHeight = '0'; // Fix vertical alignment issues
            if (backgroundColor) el.style.backgroundColor = backgroundColor;
            if (borderRadius) el.style.borderRadius = borderRadius;
            if (padding) el.style.padding = padding;
            if (rotate) el.style.transform = `rotate(${rotate}deg)`;
            if (opacity) el.style.opacity = String(opacity);

            // Create vnode
            const vnode = h(IconComponent as any, {
                size: size,
                color: color,
                strokeWidth: strokeWidth,
            });

            // Render into the element
            render(vnode, el);

            // Mark as hydrated
            el.dataset.hydrated = 'true';
        });
    };

    return { hydrateIcons };
}
