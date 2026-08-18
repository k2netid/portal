import json
import os

locales_dir = '/opt/ja-control-plane/frontend/src/modules/Operational/Member/locales'
locales = ['en.json', 'id.json', 'su.json']

additions = {
    'en': {
        'submitting': 'Submitting...'
    },
    'id': {
        'submitting': 'Mengirim...'
    },
    'su': {
        'submitting': 'Ngirimkeun...'
    }
}

for loc in locales:
    lang = loc.split('.')[0]
    filepath = os.path.join(locales_dir, loc)
    if not os.path.exists(filepath):
        continue
    with open(filepath, 'r') as f:
        data = json.load(f)
    
    if 'tickets' in data and 'form' in data['tickets']:
        data['tickets']['form']['submitting'] = additions[lang]['submitting']
    
    with open(filepath, 'w') as f:
        json.dump(data, f, indent=4, ensure_ascii=False)

print("Member Locales patched successfully.")
