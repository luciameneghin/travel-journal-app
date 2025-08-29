import { useEffect, useState } from 'react';
import PostCard from '../components/PostCard';
import { useApi } from '../context/ApiProvider';

export default function ListPage() {

  const api = useApi();

  const [items, setItems] = useState([]);

  async function fetchPosts() {
    try {
      const { data } = await api.get('/posts/list.php');
      setItems(Array.isArray(data.items) ? data.items : []);
    } catch (e) {
      console.error(e)
    }
  }

  useEffect(() => {
    fetchPosts()
  }, [api])

  return (
    <div className="max-w-5xl mx-auto px-4 py-10">
      <h1 className="text-2xl md:text-3xl font-bold">I tuoi post</h1>

      {/* Filter bar */}
      <div className="mt-6 grid grid-cols-1 md:grid-cols-3 gap-3">
        <input
          type="text"
          placeholder="Cerca titolo/descrizione/luogo…"
          className="w-full px-3 py-2 rounded-xl border"
        // TODO: value/onChange
        />
        <select
          className="px-3 py-2 rounded-xl border bg-white"
        // TODO: value/onChange
        >
          <option value="">Tutti gli umori</option>
          <option value="felice">felice</option>
          <option value="stressato">stressato</option>
          <option value="emozionato">emozionato</option>
          <option value="rilassato">rilassato</option>
          <option value="altro">altro</option>
        </select>
        <button
          className="rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2"
        // TODO: onClick: applica filtri
        >
          Filtra
        </button>
      </div>

      {/* Empty state */}
      {items.length === 0 && (
        <div className="mt-10 text-center text-gray-500">
          <div className="text-5xl mb-2">🗒️</div>
          <p>Non ci sono post da mostrare.</p>
        </div>
      )}

      {/* Grid */}
      {items.length > 0 && (
        <div className="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {items.map((p) => (
            <PostCard key={p.id} post={p} />
          ))}
        </div>
      )}



      {/* Pagination */}
      <div className="mt-10 flex items-center justify-center gap-3">
        <button
          className="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50"
        // TODO: onClick prev
        >
          ← Precedente
        </button>
        <span className="text-sm text-gray-600">
          Pagina {/* TODO: numero pagina */}
        </span>
        <button
          className="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50"
        // TODO: onClick next / disabled
        >
          Successiva →
        </button>
      </div>
    </div>
  );
}


