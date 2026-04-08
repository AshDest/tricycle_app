# 📡 API Réalisations – Guide d'intégration pour le site vitrine OKAMI

## Base URL

| Environnement | URL |
|---|---|
| **Production** | `https://tricycle.okamisarl.org/api/v1` |
| **Staging** | `https://tricycle.newtechnologyhub.org/api/v1` |
| **Local** | `http://127.0.0.1:8001/api/v1` |

---

## 🔑 Authentification

Aucune clé API requise. Tous les endpoints sont **publics** et en **lecture seule**.

---

## 📊 Endpoints

### 1. `GET /realisations` — Liste paginée

| Paramètre | Type | Défaut | Description |
|---|---|---|---|
| `per_page` | int | 12 | Items par page (max 50) |
| `categorie` | string | — | Filtrer par catégorie |
| `search` | string | — | Recherche textuelle |
| `date_from` | date | — | Date min (YYYY-MM-DD) |
| `date_to` | date | — | Date max (YYYY-MM-DD) |

```bash
curl "https://tricycle.okamisarl.org/api/v1/realisations?per_page=6&categorie=evenement"
```

**Réponse :**
```json
{
  "data": [
    {
      "id": 1,
      "titre": "Inauguration flotte Limete",
      "description": "Mise en service de 10 nouveaux tricycles...",
      "date_realisation": "2026-03-15",
      "date_realisation_formatted": "15/03/2026",
      "lieu": "Limete, Kinshasa",
      "categorie": "inauguration",
      "categorie_label": "Inauguration",
      "media_count": 3,
      "media": [
        {
          "type": "image",
          "url": "https://tricycle.okamisarl.org/storage/realisations/2026/03/img_xxx.jpg",
          "thumbnail": "https://tricycle.okamisarl.org/storage/realisations/2026/03/thumb_img_xxx.jpg",
          "original_name": "photo1.jpg",
          "size": 245000,
          "size_formatted": "239.3 KB"
        }
      ],
      "cover_image": {
        "type": "image",
        "url": "https://tricycle.okamisarl.org/storage/realisations/2026/03/img_xxx.jpg",
        "thumbnail": "https://tricycle.okamisarl.org/storage/realisations/2026/03/thumb_img_xxx.jpg"
      },
      "created_at": "2026-03-15T10:00:00.000000Z"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 6, "total": 15 }
}
```

---

### 2. `GET /realisations/latest` — Dernières réalisations

| Paramètre | Type | Défaut | Description |
|---|---|---|---|
| `limit` | int | 6 | Nombre max (max 20) |

```bash
curl "https://tricycle.okamisarl.org/api/v1/realisations/latest?limit=4"
```

---

### 3. `GET /realisations/categories` — Catégories disponibles

```bash
curl https://tricycle.okamisarl.org/api/v1/realisations/categories
```

**Réponse :**
```json
{
  "data": [
    { "value": "evenement", "label": "Événement" },
    { "value": "projet", "label": "Projet" },
    { "value": "activite", "label": "Activité" },
    { "value": "inauguration", "label": "Inauguration" },
    { "value": "formation", "label": "Formation" },
    { "value": "autre", "label": "Autre" }
  ]
}
```

---

### 4. `GET /realisations/{id}` — Détail d'une réalisation

```bash
curl https://tricycle.okamisarl.org/api/v1/realisations/5
```

---

## 🌐 Intégration dans le site vitrine

### Fichier utilitaire API

```javascript
// lib/api.js
const API_BASE = 'https://tricycle.okamisarl.org/api/v1';

export const api = {
  realisations: (params = '') =>
    fetch(`${API_BASE}/realisations?${params}`).then(r => r.json()),

  latest: (limit = 6) =>
    fetch(`${API_BASE}/realisations/latest?limit=${limit}`).then(r => r.json()),

  categories: () =>
    fetch(`${API_BASE}/realisations/categories`).then(r => r.json()),

  detail: (id) =>
    fetch(`${API_BASE}/realisations/${id}`).then(r => r.json()),
};
```

### Exemple – Page d'accueil

```javascript
async function chargerRealisations() {
  const { data } = await api.latest(6);
  const grid = document.getElementById('realisations-grid');
  grid.innerHTML = data.map(item => `
    <div class="col-md-4 mb-4">
      <div class="card h-100 shadow-sm">
        <img src="${item.cover_image?.thumbnail || '/images/placeholder.jpg'}"
             class="card-img-top" alt="${item.titre}">
        <div class="card-body">
          <span class="badge bg-primary mb-2">${item.categorie_label}</span>
          <h5 class="card-title">${item.titre}</h5>
          <p class="card-text text-muted">${item.description?.substring(0, 120)}...</p>
        </div>
        <div class="card-footer bg-white border-0">
          <small class="text-muted">${item.date_realisation_formatted} — ${item.lieu || ''}</small>
        </div>
      </div>
    </div>
  `).join('');
}
document.addEventListener('DOMContentLoaded', chargerRealisations);
```

### Exemple – Page avec filtres et pagination

```javascript
let currentPage = 1;
let currentCategorie = '';

async function chargerPage(page = 1) {
  const params = new URLSearchParams({ page, per_page: 9 });
  if (currentCategorie) params.set('categorie', currentCategorie);

  const result = await api.realisations(params.toString());
  const grid = document.getElementById('realisations-grid');
  grid.innerHTML = result.data.map(item => `...`).join('');
}

async function chargerFiltres() {
  const { data } = await api.categories();
  const select = document.getElementById('filtre-categorie');
  select.innerHTML = '<option value="">Toutes</option>' +
    data.map(c => `<option value="${c.value}">${c.label}</option>`).join('');
  select.addEventListener('change', e => {
    currentCategorie = e.target.value;
    chargerPage(1);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  chargerFiltres();
  chargerPage(1);
});
```

### Exemple React/Next.js

```jsx
// lib/api.js
const API = process.env.NEXT_PUBLIC_API_URL || 'https://tricycle.okamisarl.org/api/v1';

export async function getRealisations(limit = 6) {
  const res = await fetch(`${API}/realisations/latest?limit=${limit}`, { next: { revalidate: 300 } });
  return res.json();
}

export async function getCategories() {
  const res = await fetch(`${API}/realisations/categories`, { next: { revalidate: 3600 } });
  return res.json();
}
```

```jsx
// app/realisations/page.jsx
import { getRealisations } from '@/lib/api';

export default async function Page() {
  const { data } = await getRealisations(6);
  return (
    <div className="row">
      {data.map(item => (
        <div key={item.id} className="col-md-4 mb-4">
          <div className="card h-100">
            <img src={item.cover_image?.thumbnail} className="card-img-top" alt={item.titre} />
            <div className="card-body">
              <span className="badge bg-primary">{item.categorie_label}</span>
              <h5>{item.titre}</h5>
              <p>{item.description?.substring(0, 120)}...</p>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}
```

---

## ⚙️ Variable d'environnement

```env
NEXT_PUBLIC_API_URL=https://tricycle.okamisarl.org/api/v1
# ou
VITE_API_URL=https://tricycle.okamisarl.org/api/v1
```

---

## 📌 Notes

- Images servies depuis `https://tricycle.okamisarl.org/storage/realisations/...`
- Seules les réalisations **publiées** sont retournées
- Aucune donnée sensible exposée
- CORS ouvert en lecture (`GET`) pour tous les domaines

