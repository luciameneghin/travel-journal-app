import { useEffect, useState } from 'react';
import { useApi } from '../context/ApiProvider';
import PostCard from '../components/PostCard';

export default function ListPage() {
  const api = useApi();

  // risultati
  const [items, setItems] = useState([]);

  // filtri 
  const [q, setQ] = useState('');
  const [mood, setMood] = useState('');
  const [tags, setTags] = useState(''); // "mare, estate, spiaggia"

  // ordinamento
  const [sortBy, setSortBy] = useState('date');   // date/cost/distance
  const [order, setOrder] = useState('desc');     // asc/desc


  const [page, setPage] = useState(1);


  async function fetchPosts() {
    try {
      const params = {
        page,
        // invia solo i campi compilati
        ...(q.trim() ? { q: q.trim() } : {}),
        ...(mood ? { mood } : {}),
        ...(tags.trim()
          ? { tags: tags.split(',').map(t => t.trim()).filter(Boolean).join(',') }
          : {}),
        sortBy,
        order,
      };

      const { data } = await api.get('/posts/list.php', { params });
      setItems(Array.isArray(data.items) ? data.items : []);
    } catch (e) {
      console.error(e);
    }
  }

  // carica all’avvio e quando cambiano pagina/ordinamenti
  useEffect(() => {
    fetchPosts();
  }, [page, sortBy, order]);

  // Applica filtri (resetta alla pagina 1)
  function applyFilters() {
    setPage(1);
    fetchPosts();
  }

  // Reset filtri
  function resetFilters() {
    setQ('');
    setMood('');
    setTags('');
    setCenterLat('');
    setCenterLng('');
    setRadiusKm('');
    setSortBy('date');
    setOrder('desc');
    setPage(1);
    fetchPosts();
  }

  return (
    <div className="max-w-6xl mx-auto px-4 py-10">
      <h1 className="text-2xl md:text-3xl font-bold">I tuoi post</h1>

      {/* FILTRI */}
      <div className="mt-6 grid grid-cols-1 md:grid-cols-3 gap-3">
        <input
          type="text"
          placeholder="Cerca titolo/descrizione/luogo…"
          className="w-full px-3 py-2 rounded-xl border"
          value={q}
          onChange={(e) => setQ(e.target.value)}
        />

        <select
          className="px-3 py-2 rounded-xl border bg-white"
          value={mood}
          onChange={(e) => setMood(e.target.value)}
        >
          <option value="">Tutti gli umori</option>
          <option value="felice">felice</option>
          <option value="stressato">stressato</option>
          <option value="emozionato">emozionato</option>
          <option value="rilassato">rilassato</option>
          <option value="altro">altro</option>
        </select>

        <input
          type="text"
          placeholder="Tag (es: mare, estate)"
          className="w-full px-3 py-2 rounded-xl border"
          value={tags}
          onChange={(e) => setTags(e.target.value)}
        />
      </div>

      <div className="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div className="flex gap-2">
          <button
            className="flex-1 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2"
            onClick={applyFilters}
          >
            Applica filtri
          </button>
          <button
            className="flex-1 rounded-xl border px-4 py-2 bg-white hover:bg-gray-50"
            onClick={resetFilters}
          >
            Reset
          </button>
        </div>
      </div>

      {/* ORDINAMENTO */}
      <div className="mt-3 flex flex-wrap gap-3 items-center">
        <label className="text-sm text-gray-600">Ordina per:</label>
        <select
          className="px-3 py-2 rounded-xl border bg-white"
          value={sortBy}
          onChange={(e) => setSortBy(e.target.value)}
        >
          <option value="date">Data</option>
          <option value="cost">Spesa economica</option>
          <option value="distance">Distanza (km)</option>
        </select>

        <select
          className="px-3 py-2 rounded-xl border bg-white"
          value={order}
          onChange={(e) => setOrder(e.target.value)}
        >
          <option value="asc">Crescente</option>
          <option value="desc">Decrescente</option>
        </select>
      </div>

      {/* LISTA */}
      {items.length === 0 && (
        <div className="mt-10 text-center text-gray-500">
          <div className="text-5xl mb-2">🗒️</div>
          <p>Non ci sono post da mostrare.</p>
        </div>
      )}

      {items.length > 0 && (
        <div className="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {items.map((p) => (
            <PostCard key={p.id} post={p} />
          ))}
        </div>
      )}

      {/* PAGINAZIONE SEMPLICE */}
      <div className="mt-10 flex items-center justify-center gap-3">
        <button
          className="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50"
          onClick={() => { if (page > 1) setPage(page - 1); }}
        >
          ← Precedente
        </button>
        <span className="text-sm text-gray-600">Pagina {page}</span>
        <button
          className="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50"
          onClick={() => setPage(page + 1)}
        >
          Successiva →
        </button>
      </div>
    </div>
  );
}


