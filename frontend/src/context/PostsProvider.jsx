import { createContext, useContext, useEffect, useMemo, useState, useCallback } from 'react';
import { useApi } from './ApiProvider'

const PostsContext = createContext(null);

export function PostsProvider({ children }) {
  const api = useApi();

  const [items, setItems] = useState([]);


  const [error, setError] = useState(null);


  //fetch lista post
  const fetchList = useCallback(async () => {
    try {
      const { data } = await api.get('/posts/list.php');
      console.log(data)
      setItems(data.items || []);
    } catch (e) {
      setError('Errore nel caricamento della lista')
    }
  }, [api]);

  useEffect(() => {
    fetchList();
  }, [fetchList]);


  const value = useMemo(() => ({
    items,
    fetchList
  }), [items, fetchList])

  return (
    <PostsContext.Provider value={value}>
      {children}
    </PostsContext.Provider>
  );
}

export function usePosts() {
  const ctx = useContext(PostsContext);
  if (!ctx) throw new Error('usePosts deve essere usato dentro <PostsProvider>');
  return ctx;
}