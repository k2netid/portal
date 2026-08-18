<template>
  <div class="comments-list">
    <h3 class="text-xl font-bold text-foreground mb-4">
      {{ $t('publishing.comments.list.title') }} ({{ comments.length }})
    </h3>

    <div
      v-if="loading"
      class="text-center py-8"
    >
      <Loader2 class="w-8 h-8 mx-auto animate-spin text-muted-foreground" />
      <p class="text-muted-foreground mt-2">
        {{ $t('publishing.comments.loading') }}
      </p>
    </div>

    <div
      v-else-if="comments.length === 0"
      class="text-center py-8"
    >
      <p class="text-muted-foreground">
        {{ $t('publishing.comments.empty') }}
      </p>
    </div>

    <div
      v-else
      class="space-y-6"
    >
      <div
        v-for="comment in comments"
        :key="comment.id"
        class="border-b pb-4 last:border-b-0"
      >
        <div class="flex items-start space-x-4">
          <Avatar>
            <AvatarImage
              :src="comment.user?.avatar || ''"
              :alt="comment.user?.name || comment.name"
            />
            <AvatarFallback>{{ ((comment.user?.name || comment.name || '?')?.charAt(0) || '?').toUpperCase() }}</AvatarFallback>
          </Avatar>
          <div class="flex-1">
            <div class="flex items-center space-x-2 mb-2">
              <span class="font-semibold text-foreground">
                {{ comment.user?.name || comment.name }}
              </span>
              <span class="text-sm text-muted-foreground">
                {{ formatDate(comment.created_at) }}
              </span>
            </div>
            <p class="text-foreground">
              {{ comment.body }}
            </p>
                        
            <!-- Actions -->
            <div class="mt-2">
              <Button 
                variant="link"
                class="p-0 h-auto font-medium"
                @click="activeReplyId = activeReplyId === comment.id ? null : comment.id"
              >
                {{ $t('publishing.comments.reply') }}
              </Button>
            </div>

            <!-- Reply Form -->
            <div
              v-if="activeReplyId === comment.id"
              class="mt-4"
            >
              <CommentForm 
                :content-id="contentId" 
                :parent-id="comment.id"
                class="border-l-4 border-primary/20 pl-4"
                @submitted="handleReplySubmitted"
              />
              <Button 
                variant="ghost"
                size="sm"
                class="mt-2 ml-4 text-xs h-8"
                @click="activeReplyId = null"
              >
                {{ $t('common.actions.cancel') }}
              </Button>
            </div>
                        
            <!-- Replies -->
            <div
              v-if="comment.replies && comment.replies.length > 0"
              class="mt-4 space-y-4"
            >
              <div
                v-for="reply in comment.replies"
                :key="reply.id"
                class="flex items-start space-x-4 pl-4 border-l-2 border-muted"
              >
                <Avatar class="w-8 h-8">
                  <AvatarImage
                    :src="reply.user?.avatar || ''"
                    :alt="reply.user?.name || reply.name"
                  />
                  <AvatarFallback class="text-xs">
                    {{ ((reply.user?.name || reply.name || '?')?.charAt(0) || '?').toUpperCase() }}
                  </AvatarFallback>
                </Avatar>
                <div class="flex-1">
                  <div class="flex items-center space-x-2 mb-2">
                    <span class="font-semibold text-foreground">
                      {{ reply.user?.name || reply.name }}
                    </span>
                    <span class="text-sm text-muted-foreground">
                      {{ formatDate(reply.created_at) }}
                    </span>
                  </div>
                  <p class="text-foreground">
                    {{ reply.body }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import {
  Loader2,
} from 'lucide-vue-next';
import {
    Avatar,
    AvatarImage,
    AvatarFallback,
    Button
} from '@/shared/components/ui';
import CommentForm from '@/modules/Content/Publishing/components/comments/CommentForm.vue';

interface User {
    id: string | string;
    name: string;
    avatar?: string;
}

interface Comment {
    id: string | string;
    name?: string;
    body: string;
    created_at: string;
    user?: User;
    replies?: Comment[];
}

const props = defineProps<{
    contentId: string;
}>();

useI18n();
const comments = ref<Comment[]>([]);
const loading = ref(true);
const activeReplyId = ref<string | null>(null);

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const fetchComments = async () => {
    loading.value = true;
    try {
        const response = await api.get(publishingPaths.publicContentComments(props.contentId));
        const data = response.data;
        comments.value = Array.isArray(data) ? data : [];
    } catch (error: unknown) {
        logger.error('Error fetching comments:', error);
    } finally {
        loading.value = false;
    }
};

const handleReplySubmitted = () => {
    activeReplyId.value = null;
    fetchComments();
};

onMounted(() => {
    fetchComments();
});

defineExpose({
    refresh: fetchComments,
});
</script>


