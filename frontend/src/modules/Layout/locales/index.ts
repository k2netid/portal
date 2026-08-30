import en from './en.json';
import id from './id.json';
import su from './su.json';
import builderEn from './builder/en.json';
import builderId from './builder/id.json';
import builderSu from './builder/su.json';

export default {
    en: { ...en, builder: builderEn },
    id: { ...id, builder: builderId },
    su: { ...su, builder: builderSu },
} as const;
