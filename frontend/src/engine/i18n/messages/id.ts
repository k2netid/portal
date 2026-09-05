/**
 * Indonesian message bundle: global common strings + co-located module locales.
 */
import actions from '@/locales/id/actions.json';
import labels from '@/locales/id/labels.json';
import validation from '@/locales/id/validation.json';
import messages from '@/locales/id/messages.json';
import navigation from '@/locales/id/navigation.json';
import placeholders from '@/locales/id/placeholders.json';
import pagination from '@/locales/id/pagination.json';
import status from '@/locales/id/status.json';
import time from '@/locales/id/time.json';
import errors from '@/locales/id/errors.json';
import genders from '@/locales/id/genders.json';
import auth from '@/locales/id/auth.json';
import editor from '@/locales/id/editor.json';
import plugin from '@/locales/id/plugin.json';

import { moduleLocaleBundles } from '@/engine/i18n/moduleLocales';

const common = {
    actions,
    labels,
    validation,
    messages,
    navigation,
    placeholders,
    pagination,
    status,
    time,
    errors,
    genders,
    auth,
};

export default {
    common,
    shared: common,
    editor,
    plugin,
    ...moduleLocaleBundles.id,
};
