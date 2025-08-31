# Travel Journal App

Un’app semplice per annotare viaggi/esperienze, con **titolo**, **data**, **descrizione**, **umore**, **costi**, **luogo**, **tag** e **immagini**.

Tecnologie utilizzate: **MySQL** (DB) · **PHP 8** + **PDO** (backend API) · **React + Vite** (frontend) · **Axios** (HTTP) · **Tailwind** (UI).

## 🎯 Scopo del progetto

Offrire un diario di viaggio minimale ma completo: creare e consultare post con dettagli testuali, tag, e media.

## 🗃️ Database — perché MySQL

### Schema

- posts: titolo, data, descrizione, umore, sforzi, costo EUR, luogo (lat/lng)
- tags: tag univoci.
- post_tags: relazione molti-a-molti tra post e tag.
- media: collegati al post; per ora filename (immagine).

### Motivazioni

- Relazionale, join semplici, integrità referenziale.


## 🧩 Backend — PHP 8 + PDO
### Capacità

- API JSON per: crea, lista con filtri/ordinamenti, dettaglio.
- CORS configurato per Vite in locale.

### Media

- Asset statici in backend/public/src/images.
- In DB salviamo solo il filename; l’API restituisce URL assoluti (es. http://localhost:8000/src/images/<file>), così in FE basta src={m.url}.


## 🎨 Frontend 
### React + Vite

- ApiProvider: istanza Axios condivisa (baseURL da .env → VITE_API_BASE).
- PostsProvider: fetch della lista e get by id con piccola cache in memoria.

### Pagine

- ListPage: griglia card (PostCard), filtri (testo, mood, tag, raggio opz.), ordinamenti (data, costo, distanza), paginazione base.
- DetailPage: dettaglio post + sezione Media (placeholder “Nessuna immagine” se mancano).

### Motivazioni

- Vite: velocissimo in dev, configurazione minima.
- React: stato e componenti riusabili.
- Axios: API semplice e gestione errori.
- Tailwind: stile consistente senza CSS complesso.