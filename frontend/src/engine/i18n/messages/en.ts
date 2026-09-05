/**
 * English message bundle: global common strings + co-located module locales.
 */
import actions from '@/locales/en/actions.json';
import labels from '@/locales/en/labels.json';
import validation from '@/locales/en/validation.json';
import messages from '@/locales/en/messages.json';
import navigation from '@/locales/en/navigation.json';
import placeholders from '@/locales/en/placeholders.json';
import pagination from '@/locales/en/pagination.json';
import status from '@/locales/en/status.json';
import time from '@/locales/en/time.json';
import errors from '@/locales/en/errors.json';
import genders from '@/locales/en/genders.json';
import auth from '@/locales/en/auth.json';
import editor from '@/locales/en/editor.json';
import plugin from '@/locales/en/plugin.json';

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
    ...moduleLocaleBundles.en,
};
