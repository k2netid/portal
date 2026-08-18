<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h3 class="text-lg font-medium">{{ t('system.security.abac.title') }}</h3>
        <p class="text-sm text-muted-foreground">
          {{ t('system.security.abac.subtitle') }}
        </p>
      </div>
      <Button @click="openCreateModal">
        <Plus class="w-4 h-4 mr-2" />
        {{ t('system.security.abac.create') }}
      </Button>
    </div>

    <Card>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>{{ t('system.security.abac.columns.name') }}</TableHead>
            <TableHead>{{ t('system.security.abac.columns.target') }}</TableHead>
            <TableHead>{{ t('system.security.abac.columns.action') }}</TableHead>
            <TableHead>{{ t('system.security.abac.columns.status') }}</TableHead>
            <TableHead class="text-right">{{ t('system.security.abac.columns.actions') }}</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell colspan="5" class="text-center py-8">
              <Loader2 class="w-6 h-6 animate-spin mx-auto text-muted-foreground" />
            </TableCell>
          </TableRow>
          <TableRow v-else-if="policies.length === 0">
            <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
              {{ t('system.security.abac.empty') }}
            </TableCell>
          </TableRow>
          <TableRow v-else v-for="policy in policies" :key="policy.id">
            <TableCell class="font-medium">
              {{ policy.name }}
              <div class="text-xs text-muted-foreground mt-1">{{ policy.description }}</div>
            </TableCell>
            <TableCell>
              <Badge variant="outline">{{ policy.target_resource || '*' }}</Badge>
            </TableCell>
            <TableCell>
              <Badge variant="secondary">{{ policy.action || '*' }}</Badge>
            </TableCell>
            <TableCell>
              <Badge :variant="policy.is_active ? 'default' : 'secondary'">
                {{ policy.is_active ? t('system.security.abac.status.active') : t('system.security.abac.status.inactive') }}
              </Badge>
            </TableCell>
            <TableCell class="text-right">
              <Button variant="ghost" size="icon" @click="editPolicy(policy)">
                <Pencil class="w-4 h-4" />
              </Button>
              <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive" @click="confirmDelete(policy)">
                <Trash2 class="w-4 h-4" />
              </Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </Card>

    <!-- Modal Form -->
    <Dialog v-model:open="isModalOpen">
      <DialogContent class="console-dialog-lg sm:max-w-[600px] bg-card border border-border/80 rounded-xl">
        <DialogHeader>
          <DialogTitle>{{ editingPolicy ? t('system.security.abac.form.editTitle') : t('system.security.abac.form.createTitle') }}</DialogTitle>
          <DialogDescription>
            {{ t('system.security.abac.form.description') }}
          </DialogDescription>
        </DialogHeader>

        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label for="name">{{ t('system.security.abac.form.name') }}</Label>
            <Input id="name" v-model="form.name" placeholder="e.g. Require KYC Level 2 for Reports" />
          </div>
          <div class="grid gap-2">
            <Label for="description">{{ t('system.security.abac.form.descriptionField') }}</Label>
            <Textarea id="description" v-model="form.description" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="grid gap-2">
              <Label for="target">{{ t('system.security.abac.form.target') }}</Label>
              <Input id="target" v-model="form.target_resource" placeholder="e.g. financial_reports or *" />
            </div>
            <div class="grid gap-2">
              <Label for="action">{{ t('system.security.abac.form.action') }}</Label>
              <Input id="action" v-model="form.action" placeholder="e.g. view or *" />
            </div>
          </div>
          <div class="grid gap-2">
            <Label>{{ t('system.security.abac.form.conditions') }}</Label>
            <Textarea 
              v-model="form.conditions" 
              class="font-mono text-xs min-h-[150px]" 
              placeholder="[{&quot;attribute&quot;: &quot;user.kyc_level&quot;, &quot;operator&quot;: &quot;>=&quot;, &quot;value&quot;: 2}]"
            />
            <p class="text-xs text-muted-foreground">
              Format: [{"attribute": "path", "operator": "==", "value": "x"}]
            </p>
          </div>
          <div class="flex items-center gap-2">
            <Switch id="active" v-model:checked="form.is_active" />
            <Label for="active">{{ t('system.security.abac.form.active') }}</Label>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="isModalOpen = false">{{ t('common.actions.cancel') }}</Button>
          <Button @click="savePolicy" :disabled="saving">
            <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
            {{ t('system.security.abac.form.save') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Plus, Pencil, Trash2, Loader2 } from 'lucide-vue-next';
import { 
  Card, Button, Input, Label, Textarea, Badge, Switch,
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
} from '@/shared/components/ui';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';

const toast = useToast();
const { confirm } = useConfirm();
const { t } = useI18n();

interface Policy {
  id: string;
  name: string;
  description: string | null;
  target_resource: string;
  action: string;
  conditions: any[];
  is_active: boolean;
}

const policies = ref<Policy[]>([]);
const loading = ref(true);
const saving = ref(false);
const isModalOpen = ref(false);
const editingPolicy = ref<Policy | null>(null);

const form = ref({
  name: '',
  description: '',
  target_resource: '*',
  action: '*',
  conditions: '[\n  {\n    "attribute": "user.kyc_level",\n    "operator": ">=",\n    "value": "level_2"\n  }\n]',
  is_active: true
});

const fetchPolicies = async () => {
  loading.value = true;
  try {
    const response = await api.get('/manage/security/abac-policies');
    const data = response.data;
    policies.value = Array.isArray(data) ? data : [];
  } catch (err: any) {
    toast.error.fromResponse(err);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingPolicy.value = null;
  form.value = {
    name: '',
    description: '',
    target_resource: '*',
    action: '*',
    conditions: '[\n  {\n    "attribute": "user.kyc_level",\n    "operator": ">=",\n    "value": "level_2"\n  }\n]',
    is_active: true
  };
  isModalOpen.value = true;
};

const editPolicy = (policy: Policy) => {
  editingPolicy.value = policy;
  form.value = {
    name: policy.name,
    description: policy.description || '',
    target_resource: policy.target_resource,
    action: policy.action,
    conditions: JSON.stringify(policy.conditions, null, 2),
    is_active: policy.is_active
  };
  isModalOpen.value = true;
};

const savePolicy = async () => {
  try {
    // Validate JSON
    let parsedConditions = [];
    try {
      parsedConditions = JSON.parse(form.value.conditions);
      if (!Array.isArray(parsedConditions)) throw new Error('Conditions must be an array');
    } catch (e) {
      toast.error.default('Conditions must be a valid JSON array');
      return;
    }

    saving.value = true;
    const payload = {
      ...form.value,
      conditions: parsedConditions
    };

    if (editingPolicy.value) {
      await api.put(`/manage/security/abac-policies/${editingPolicy.value.id}`, payload);
      toast.success.update('policy');
    } else {
      await api.post('/manage/security/abac-policies', payload);
      toast.success.create('policy');
    }

    isModalOpen.value = false;
    fetchPolicies();
  } catch (err: any) {
    toast.error.fromResponse(err);
  } finally {
    saving.value = false;
  }
};

const confirmDelete = async (policy: Policy) => {
  const ok = await confirm({
    title: t('system.security.abac.deleteTitle'),
    message: t('system.security.abac.confirmDelete', { name: policy.name }),
    confirmText: t('common.actions.delete', 'Delete'),
    variant: 'destructive',
  });
  if (!ok) return;
  
  try {
    await api.delete(`/manage/security/abac-policies/${policy.id}`);
    toast.success.delete('policy');
    fetchPolicies();
  } catch (err: any) {
    toast.error.fromResponse(err);
  }
};

onMounted(fetchPolicies);
</script>
