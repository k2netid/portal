/**
 * Sundanese message bundle: global common strings + co-located module locales.
 */
import actions from '@/locales/su/actions.json';
import labels from '@/locales/su/labels.json';
import validation from '@/locales/su/validation.json';
import messages from '@/locales/su/messages.json';
import navigation from '@/locales/su/navigation.json';
import placeholders from '@/locales/su/placeholders.json';
import pagination from '@/locales/su/pagination.json';
import status from '@/locales/su/status.json';
import time from '@/locales/su/time.json';
import errors from '@/locales/su/errors.json';
import genders from '@/locales/su/genders.json';
import auth from '@/locales/su/auth.json';
import media from '@/locales/su/media.json';
import editor from '@/locales/su/editor.json';
import ai from '@/locales/su/ai.json';

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
    media,
    editor,
    ai,
    publishing: {
        editor,
        content: {
            form: {
                maxSizeHint: 'Maks {size}MB',
                minHint: 'Min {dimensions}'
            }
        }
    },
    ...moduleLocaleBundles.su,
};
