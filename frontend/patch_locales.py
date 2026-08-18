import json
import os

locales_dir = '/opt/ja-control-plane/frontend/src/modules/Crm/locales'
locales = ['en.json', 'id.json', 'su.json']

additions = {
    'en': {
        'notes': {
            'title': 'Notes',
            'empty': 'No notes yet.',
            'new': 'New note',
            'edit': 'Edit note',
            'body': 'Note content'
        },
        'bulk': {
            'confirmUpdate': 'Update selected records?'
        }
    },
    'id': {
        'notes': {
            'title': 'Catatan',
            'empty': 'Belum ada catatan.',
            'new': 'Catatan baru',
            'edit': 'Edit catatan',
            'body': 'Isi catatan'
        },
        'bulk': {
            'confirmUpdate': 'Perbarui data terpilih?'
        }
    },
    'su': {
        'notes': {
            'title': 'Catetan',
            'empty': 'Teu acan aya catetan.',
            'new': 'Catetan anyar',
            'edit': 'Édit catetan',
            'body': 'Eusi catetan'
        },
        'bulk': {
            'confirmUpdate': 'Anyarkeun data nu dipilih?'
        }
    }
}

for loc, filepath in [(loc, os.path.join(locales_dir, f"{loc}.json")) for loc in additions.keys()]:
    if not os.path.exists(filepath):
        continue
    with open(filepath, 'r') as f:
        data = json.load(f)
    
    # Merge notes
    if 'notes' not in data:
        data['notes'] = {}
    data['notes'].update(additions[loc]['notes'])
    
    # Merge bulk
    if 'bulk' not in data:
        data['bulk'] = {}
    data['bulk'].update(additions[loc]['bulk'])
    
    with open(filepath, 'w') as f:
        json.dump(data, f, indent=2, ensure_ascii=False)

print("Locales patched successfully.")
