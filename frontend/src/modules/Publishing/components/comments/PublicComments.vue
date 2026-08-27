<template>
  <div class="public-comments-container w-full pt-10 mt-12 border-t border-border/70">
    <!-- Header with Count -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
          <MessageSquare class="w-5 h-5" />
        </div>
        <div>
          <h3 class="text-xl sm:text-2xl font-black text-foreground">
            {{ title || t('publishing.comments.public.title', 'Komentar & Diskusi') }}
          </h3>
          <p class="text-xs text-muted-foreground">
            {{ totalCommentsCount }} {{ totalCommentsCount === 1 ? t('publishing.comments.public.singleCount', 'tanggapan') : t('publishing.comments.public.multiCount', 'tanggapan') }}
          </p>
        </div>
      </div>

      <button
        v-if="!showForm && isCommentsOpen"
        type="button"
        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary/10 hover:bg-primary/20 text-primary text-xs font-bold transition-colors"
        @click="showForm = true"
      >
        <Plus class="w-3.5 h-3.5" />
        {{ t('publishing.comments.public.writeComment', 'Tulis Komentar') }}
      </button>
    </div>

    <!-- Closed Notice -->
    <div
      v-if="!isCommentsOpen"
      class="p-4 rounded-xl bg-muted/50 border border-border text-center text-xs text-muted-foreground mb-8"
    >
      {{ t('publishing.comments.public.closed', 'Kolom komentar ditutup untuk artikel ini.') }}
    </div>

    <!-- Main Comment Form -->
    <div
      v-if="isCommentsOpen && showForm"
      class="mb-10 p-5 sm:p-6 rounded-2xl border border-border bg-card/60 shadow-sm backdrop-blur-sm space-y-4"
    >
      <div class="flex items-center justify-between pb-2 border-b border-border/50">
        <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">
          {{ t('publishing.comments.public.leaveComment', 'Tinggalkan Komentar') }}
        </span>
        <button
          v-if="replyToCommentId"
          type="button"
          class="text-xs text-destructive hover:underline font-medium"
          @click="cancelReply"
        >
          {{ t('publishing.comments.public.cancelReply', 'Batal Balas') }}
        </button>
      </div>

      <!-- Reply Indicator -->
      <div
        v-if="replyToCommentId"
        class="p-3 rounded-xl bg-primary/5 border border-primary/20 flex items-center justify-between text-xs text-primary"
      >
        <span>
          {{ t('publishing.comments.public.replyingTo', 'Membalas ke') }}
          <strong>@{{ replyToAuthorName }}</strong>
        </span>
      </div>

      <!-- Success Notification -->
      <div
        v-if="submitSuccessMessage"
        class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs flex items-center gap-2"
      >
        <CheckCircle2 class="w-4 h-4 flex-shrink-0" />
        <span>{{ submitSuccessMessage }}</span>
      </div>

      <!-- Error Notification -->
      <div
        v-if="submitErrorMessage"
        class="p-4 rounded-xl bg-destructive/10 border border-destructive/20 text-destructive text-xs flex items-center gap-2"
      >
        <AlertCircle class="w-4 h-4 flex-shrink-0" />
        <span>{{ submitErrorMessage }}</span>
      </div>

      <form
        class="space-y-4"
        @submit.prevent="submitComment"
      >
        <!-- Logged in status info -->
        <div
          v-if="authStore.isAuthenticated && authStore.user"
          class="flex items-center gap-3 p-3 rounded-xl bg-muted/40 border border-border/60"
        >
          <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
            {{ getInitials(authStore.user.name) }}
          </div>
          <div class="text-xs">
            <span class="text-muted-foreground">{{ t('publishing.comments.public.loggedInAs', 'Komentar sebagai') }}</span>
            <strong class="text-foreground ml-1">{{ authStore.user.name }}</strong>
            <span class="text-muted-foreground ml-1">({{ authStore.user.email }})</span>
          </div>
        </div>

        <!-- Guest Inputs (Name & Email) -->
        <div
          v-else
          class="grid grid-cols-1 sm:grid-cols-2 gap-4"
        >
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-foreground">
              {{ t('publishing.comments.public.name', 'Nama Lengkap') }} <span class="text-destructive">*</span>
            </label>
            <input
              v-model="guestName"
              type="text"
              required
              :placeholder="t('publishing.comments.public.namePlaceholder', 'Masukkan nama Anda...')"
              class="w-full h-10 px-3.5 text-xs rounded-xl border border-border bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
            >
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-foreground">
              {{ t('publishing.comments.public.email', 'Email (Privat)') }} <span class="text-destructive">*</span>
            </label>
            <input
              v-model="guestEmail"
              type="email"
              required
              :placeholder="t('publishing.comments.public.emailPlaceholder', 'Enter your email')"
              class="w-full h-10 px-3.5 text-xs rounded-xl border border-border bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
            >
          </div>
        </div>

        <!-- Comment Textarea -->
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-foreground">
            {{ t('publishing.comments.public.message', 'Pesan Komentar') }} <span class="text-destructive">*</span>
          </label>
          <textarea
            v-model="commentBody"
            rows="4"
            required
            :placeholder="placeholder || t('publishing.comments.public.bodyPlaceholder', 'Tulis tanggapan atau pertanyaan Anda secara sopan...')"
            class="w-full p-3.5 text-xs rounded-xl border border-border bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all resize-y"
          />
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-between pt-1">
          <span class="text-[11px] text-muted-foreground">
            {{ t('publishing.comments.public.moderationNotice', 'Komentar akan melalui peninjauan sebelum dipublikasikan.') }}
          </span>
          <button
            type="submit"
            :disabled="submitting || !commentBody.trim()"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-primary-foreground text-xs font-bold hover:bg-primary/90 disabled:opacity-50 transition-all shadow-sm"
          >
            <Loader2
              v-if="submitting"
              class="w-3.5 h-3.5 animate-spin"
            />
            <Send
              v-else
              class="w-3.5 h-3.5"
            />
            {{ submitText || t('publishing.comments.public.submit', 'Kirim Komentar') }}
          </button>
        </div>
      </form>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="py-12 flex flex-col items-center justify-center gap-3 text-muted-foreground"
    >
      <Loader2 class="w-6 h-6 animate-spin text-primary" />
      <span class="text-xs">{{ t('publishing.comments.public.loading', 'Memuat komentar...') }}</span>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="comments.length === 0"
      class="py-12 text-center rounded-2xl border border-dashed border-border/80 bg-card/30 p-8 space-y-3"
    >
      <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mx-auto text-muted-foreground">
        <MessageSquare class="w-5 h-5 opacity-60" />
      </div>
      <h4 class="text-sm font-bold text-foreground">
        {{ t('publishing.comments.public.emptyTitle', 'Belum Ada Komentar') }}
      </h4>
      <p class="text-xs text-muted-foreground max-w-sm mx-auto">
        {{ t('publishing.comments.public.emptyDesc', 'Jadilah yang pertama untuk membagikan pandangan atau pertanyaan Anda!') }}
      </p>
    </div>

    <!-- Comments List -->
    <div
      v-else
      class="space-y-6"
    >
      <template
        v-for="comment in comments"
        :key="comment.id"
      >
        <div class="comment-thread group p-4 sm:p-5 rounded-2xl border border-border/70 bg-card/40 hover:bg-card/70 hover:border-border transition-all">
          <!-- Main Comment Card -->
          <div class="flex items-start gap-3 sm:gap-4">
            <!-- Avatar -->
            <div class="flex-shrink-0">
              <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-primary/10 border border-primary/20 text-primary flex items-center justify-center font-bold text-xs sm:text-sm">
                {{ getInitials(comment.user?.name || comment.name || 'User') }}
              </div>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                  <span class="text-xs sm:text-sm font-bold text-foreground">
                    {{ comment.user?.name || comment.name || t('publishing.comments.detail.anonymous', 'Tamu') }}
                  </span>
                  <span
                    v-if="comment.user"
                    class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-primary/10 text-primary"
                  >
                    Member
                  </span>
                </div>
                <span class="text-[11px] text-muted-foreground flex items-center gap-1 font-mono">
                  <Clock class="w-3 h-3" />
                  {{ formatDate(comment.created_at) }}
                </span>
              </div>

              <!-- Comment Body -->
              <p class="text-xs sm:text-sm text-foreground/90 leading-relaxed whitespace-pre-line pt-1">
                {{ comment.body }}
              </p>

              <!-- Actions -->
              <div
                v-if="isCommentsOpen"
                class="pt-2 flex items-center gap-4 text-xs"
              >
                <button
                  type="button"
                  class="inline-flex items-center gap-1 text-muted-foreground hover:text-primary transition-colors font-semibold text-[11px]"
                  @click="startReply(comment)"
                >
                  <Reply class="w-3 h-3" />
                  {{ t('publishing.comments.public.reply', 'Balas') }}
                </button>
              </div>
            </div>
          </div>

          <!-- Nested Replies -->
          <div
            v-if="comment.replies && comment.replies.length > 0"
            class="mt-4 pt-4 border-t border-border/40 pl-6 sm:pl-10 space-y-4 border-l-2 border-l-primary/20 ml-4 sm:ml-5"
          >
            <div
              v-for="reply in comment.replies"
              :key="reply.id"
              class="flex items-start gap-3"
            >
              <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-muted text-foreground flex items-center justify-center font-bold text-[10px] sm:text-xs flex-shrink-0">
                {{ getInitials(reply.user?.name || reply.name || 'User') }}
              </div>
              <div class="flex-1 min-w-0 space-y-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-foreground">
                      {{ reply.user?.name || reply.name || t('publishing.comments.detail.anonymous', 'Tamu') }}
                    </span>
                    <span
                      v-if="reply.user"
                      class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-muted text-muted-foreground"
                    >
                      Staff
                    </span>
                  </div>
                  <span class="text-[10px] text-muted-foreground font-mono">
                    {{ formatDate(reply.created_at) }}
                  </span>
                </div>
                <p class="text-xs text-foreground/80 leading-relaxed whitespace-pre-line">
                  {{ reply.body }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { PublishingService } from '@/modules/Publishing/services/publishingService';
import {
  MessageSquare,
  Plus,
  Send,
  Loader2,
  Clock,
  Reply,
  CheckCircle2,
  AlertCircle
} from 'lucide-vue-next';

interface CommentItem {
  id: string;
  content_id: string;
  user_id?: string | null;
  parent_id?: string | null;
  name?: string;
  email?: string;
  body: string;
  status: string;
  created_at: string;
  user?: {
    id: string;
    name: string;
    email: string;
  } | null;
  replies?: CommentItem[];
}

const props = withDefaults(
  defineProps<{
    contentId: string;
    isCommentsOpen?: boolean;
    title?: string;
    placeholder?: string;
    submitText?: string;
  }>(),
  {
    isCommentsOpen: true,
    title: '',
    placeholder: '',
    submitText: '',
  }
);

const { t } = useI18n();
const authStore = useAuthStore();

const comments = ref<CommentItem[]>([]);
const loading = ref(false);
const submitting = ref(false);
const showForm = ref(true);

const guestName = ref('');
const guestEmail = ref('');
const commentBody = ref('');
const replyToCommentId = ref<string | null>(null);
const replyToAuthorName = ref('');

const submitSuccessMessage = ref('');
const submitErrorMessage = ref('');

const totalCommentsCount = computed(() => {
  let count = comments.value.length;
  for (const c of comments.value) {
    if (c.replies && c.replies.length > 0) {
      count += c.replies.length;
    }
  }
  return count;
});

const getInitials = (name?: string): string => {
  if (!name) return 'U';
  return name.trim().charAt(0).toUpperCase();
};

const formatDate = (dateStr?: string): string => {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  } catch {
    return dateStr;
  }
};

const fetchComments = async () => {
  if (!props.contentId) return;
  loading.value = true;
  try {
    const response = await PublishingService.publicComments(props.contentId);
    comments.value = (response.data?.data || response.data || []) as CommentItem[];
  } catch (error) {
    console.error('Failed to fetch comments:', error);
  } finally {
    loading.value = false;
  }
};

const startReply = (comment: CommentItem) => {
  replyToCommentId.value = comment.id;
  replyToAuthorName.value = comment.user?.name || comment.name || 'User';
  showForm.value = true;
};

const cancelReply = () => {
  replyToCommentId.value = null;
  replyToAuthorName.value = '';
};

const submitComment = async () => {
  if (!props.contentId || !commentBody.value.trim()) return;

  submitting.value = true;
  submitSuccessMessage.value = '';
  submitErrorMessage.value = '';

  try {
    const payload: {
      body: string;
      name?: string;
      email?: string;
      parent_id?: string;
    } = {
      body: commentBody.value.trim(),
    };

    if (replyToCommentId.value) {
      payload.parent_id = replyToCommentId.value;
    }

    if (!authStore.isAuthenticated) {
      payload.name = guestName.value.trim();
      payload.email = guestEmail.value.trim();
    }

    const response = await PublishingService.postPublicComment(props.contentId, payload);
    const newComment = (response.data?.data || response.data) as CommentItem;

    if (newComment && newComment.status === 'approved') {
      submitSuccessMessage.value = t('publishing.comments.public.postedSuccess', 'Komentar Anda telah berhasil dipublikasikan!');
      if (payload.parent_id) {
        const parent = comments.value.find((c) => c.id === payload.parent_id);
        if (parent) {
          if (!parent.replies) parent.replies = [];
          parent.replies.push(newComment);
        }
      } else {
        comments.value.unshift(newComment);
      }
    } else {
      submitSuccessMessage.value = t('publishing.comments.public.pendingSuccess', 'Terima kasih! Komentar Anda telah diterima dan sedang menunggu persetujuan moderator.');
    }

    // Reset form
    commentBody.value = '';
    cancelReply();
  } catch (error: unknown) {
    const msg = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || t('publishing.comments.public.failed', 'Gagal mengirim komentar. Silakan coba kembali.');
    submitErrorMessage.value = msg;
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  void fetchComments();
});

watch(() => props.contentId, () => {
  void fetchComments();
});
</script>
